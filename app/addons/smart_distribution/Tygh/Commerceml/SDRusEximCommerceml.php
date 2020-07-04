<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

namespace Tygh\Commerceml;

use Tygh\Tygh;
use Tygh\Settings;
use Tygh\Registry;
use Tygh\Storage;
use Tygh\Enum\ProductFeatures;
use Tygh\Database\Connection;
use Tygh\Enum\ProductTracking;
use Tygh\Bootstrap;
use Tygh\Addons\ProductVariations\Product\Manager as ProductManager;
use Tygh\Enum\ImagePairTypes;

class SDRusEximCommerceml extends RusEximCommerceml 
{
    public $prices_commerseml = array();
    
    public function importCategoriesFile($data_categories, $import_params, $parent_id = 0)
    {
        $categories_import = array();
        $cml = $this->cml;
        $default_category = $this->s_commerceml['exim_1c_default_category'];
        $link_type = $this->s_commerceml['exim_1c_import_type_categories'];

        if (isset($data_categories -> {$cml['group']})) {
            foreach ($data_categories -> {$cml['group']} as $_group) {
                $category_ids = $this->getCompanyIdByLinkType($link_type, $_group);

                $category_id = 0;
                if (!empty($category_ids)) {
                    $category_id = $this->db->getField("SELECT category_id FROM ?:categories WHERE category_id IN (?a) AND company_id = ?i", $category_ids, $this->company_id);
                }

                if (empty($category_id)) {
                    $this->addMessageLog("New category: " . strval($_group -> {$this->cml['name']}));
                }

                $category_data = $this->getDataCategoryByFile($_group, $category_id, $parent_id, $import_params['lang_code']);

                if ($import_params['user_data']['user_type'] != 'V' && !Registry::get('runtime.company_id')) {
                    $category_id = fn_update_category($category_data, $category_id, $import_params['lang_code']);
                    $this->addMessageLog("Add category: " . $category_data['category']);
                } else {
                    $category_id = $default_category;
                    // [csmarket] get extra categories
                    $id = $this->db->getField("SELECT category_id FROM ?:category_descriptions WHERE lang_code = ?s AND (category = ?s OR alternative_names LIKE ?l) ", $import_params['lang_code'], strval($_group -> {$cml['name']}), '%'.strval($_group -> {$cml['name']}).'%' );

                    
                    if (!empty($id)) {
                        $category_id = $id;
                    }
                }

                $categories_import[strval($_group -> {$cml['id']})] = $category_id;
                if (isset($_group -> {$cml['groups']} -> {$cml['group']})) {
                    $this->importCategoriesFile($_group -> {$cml['groups']}, $import_params, $category_id);
                    
                }
            }
            if (!empty($this->categories_commerceml)) {
                $_categories_commerceml = $this->categories_commerceml;
                $this->categories_commerceml = fn_array_merge($_categories_commerceml, $categories_import);
            } else {
                $this->categories_commerceml = $categories_import;
            }


            if (!empty($this->categories_commerceml)) {
                \Tygh::$app['session']['exim_1c']['categories_commerceml'] = $this->categories_commerceml;
            }
        }
    }


    public function dataProductPrice($product_prices, $prices_commerseml)
    {
        $cml = $this->cml;
        $prices = array();
        $list_prices = array();
        foreach ($product_prices as $external_id => $p_price) {
            foreach ($prices_commerseml as $p_commerseml) {
                if (!empty($p_commerseml['external_id'])) {
                    if ($external_id == $p_commerseml['external_id']) {
                        if ($p_commerseml['type'] == 'base') {
                            $prices['base_price'] = $p_price['price'];
                        }

                        if (($p_commerseml['type'] == 'list')) {
                            $prices['list_price'] = $p_price['price'];
                            $list_prices[] = $p_price['price'];
                        }

                        if ($p_commerseml['type'] == 'user_price') {
                            $prices['user_price'][] = array(
                                'price' => $p_price['price'],
                                'user_id' => $p_commerseml['user_id']
                            );
                        }

                        if ($p_commerseml['usergroup_id'] > 0) {
                            $prices['qty_prices'][] = array(
                                'price' => $p_price['price'],
                                'usergroup_id' => $p_commerseml['usergroup_id']
                            );
                        }
                    }
                }
            }
        }

        if (!empty($prices['list_price']) && !empty($prices['base_price'])) {
            if ($prices['list_price'] < $prices['base_price']) {
                $prices['list_price'] = 0;

                foreach ($list_prices as $list_price) {
                    if ($list_price >= $prices['base_price']) {
                        $prices['list_price'] = $list_price;
                    }
                }
            }
        }

        if (empty($prices['base_price']) && (!empty($prices['qty_prices']) || !empty($prices['user_price']))) {
            $_prices = fn_array_merge($prices['qty_prices'], $prices['user_price'], false);
            $p = fn_array_column($_prices, 'price');
            $prices['base_price'] = max($p);
        }

        return $prices;
    }

    public function importProductOffersFile($data_offers, $import_params)
    {
        $prices_commerseml = &$this->prices_commerseml;
        if (!empty(\Tygh::$app['session']['exim_1c']['prices_commerseml'])) {
            $prices_commerseml = \Tygh::$app['session']['exim_1c']['prices_commerseml'];
        }
        $cml = $this->cml;
        $create_prices = $this->s_commerceml['exim_1c_create_prices'];
        $schema_version = $this->s_commerceml['exim_1c_schema_version'];
        $import_mode = $this->s_commerceml['exim_1c_import_mode_offers'];
        $negative_amount = Registry::get('settings.General.allow_negative_amount');

        $all_currencies = $this->dataProductCurrencies();

        if (isset($data_offers -> {$cml['prices_types']} -> {$cml['price_type']})) {
            $price_offers = $this->dataPriceOffers($data_offers -> {$cml['prices_types']});

            if ($create_prices == 'Y') {
                $data_prices = $this->db->getArray(
                    'SELECT price_1c, type, usergroup_id FROM ?:rus_exim_1c_prices WHERE company_id = ?i',
                    $this->company_id
                );

                if (empty($data_prices)) {
                    $data_prices = $this->db->getArray(
                        'SELECT price_1c, type, usergroup_id FROM ?:rus_exim_1c_prices'
                    );
                }
                if (empty($prices_commerseml)) $prices_commerseml = $this->getPricesDataFromFile($data_offers -> {$cml['prices_types']}, $data_prices);
            }
        }

        if (!isset(\Tygh::$app['session']['exim_1c']['import_offers'])) {
            $offer_pos_start = 0;
        } else {
            $offer_pos_start = \Tygh::$app['session']['exim_1c']['import_offers'];
        }

        if ($import_params['service_exchange'] == '') {
            if (count($data_offers -> {$cml['offers']} -> {$cml['offer']}) > COUNT_1C_IMPORT) {
                if ((count($data_offers -> {$cml['offers']} -> {$cml['offer']}) - $offer_pos_start) > COUNT_1C_IMPORT) {
                    fn_echo("progress\n");
                } else {
                    fn_echo("success\n");
                }

            } else {
                fn_echo("success\n");
            }
        }

        $offers_pos = 0;
        $progress = false;
        $count_import_offers = 0;
        foreach ($data_offers -> {$cml['offers']} -> {$cml['offer']} as $offer) {
            $offers_pos++;

            if ($offers_pos < $offer_pos_start) {
                continue;
            }

            if ($offers_pos - $offer_pos_start + 1 > COUNT_1C_IMPORT && $import_params['service_exchange'] == '') {
                $progress = true;
                break;
            }

            $product = array();
            $combination_id = 0;
            $ids = fn_explode('#', strval($offer -> {$cml['id']}));
            $guid_product = array_shift($ids);

            if (!empty($ids)) {
                $combination_id = reset($ids);
            }

            // [csmarket]
            $company_condition = fn_get_company_condition('company_id', true, '', false, true);
            $product_data = $this->db->getRow("SELECT product_id, update_1c, status, tracking, product_code, timestamp FROM ?:products WHERE external_id = ?s $company_condition", $guid_product);
            if (empty($product_data)) {
                $_product_data = $this->getProductDataByLinkType($link_type, $offer, $cml);
                if (!empty($_product_data))
                $product_data = $this->db->getRow("SELECT product_id, update_1c, status, tracking, product_code, timestamp FROM ?:products WHERE product_id = ?s $company_condition", $_product_data['product_id']);
            }
            $product_id = !empty($product_data['product_id']) ? $product_data['product_id'] : 0;

            if (!($this->checkImportPrices($product_data))) {
                continue;
            }

            $count_import_offers++;

            if (isset($offer -> {$cml['amount']})) {
                $amount = strval($offer -> {$cml['amount']});

            } elseif (isset($offer -> {$cml['store']})) {
                foreach ($offer -> {$cml['store']} as $store) {
                    $amount += strval($store[$cml['in_stock']]);
                }
            }

            $prices = array();
            if (isset($offer -> {$cml['prices']}) && !empty($price_offers)) {
                $_price_offers = $price_offers;

                foreach ($offer -> {$cml['prices']} -> {$cml['price']} as $c_price) {
                    if (!empty($c_price -> {$cml['currency']}) && !empty($_price_offers[strval($c_price -> {$cml['price_id']})]['coefficient']) && !empty($all_currencies[strval($c_price -> {$cml['currency']})]['coefficient'])) {
                        $_price_offers[strval($c_price -> {$cml['price_id']})]['coefficient'] = $all_currencies[strval($c_price -> {$cml['currency']})]['coefficient'];
                    }
                }

                $product_prices = $this->conversionProductPrices($offer -> {$cml['prices']} -> {$cml['price']}, $_price_offers);

                if ($create_prices == 'Y') {
                    $prices = $this->dataProductPrice($product_prices, $prices_commerseml);
                    if (empty($prices) && (!empty($product_prices[strval($offer -> {$cml['prices']} -> {$cml['price']} -> {$cml['price_id']})]['price']))) {
                        $prices['base_price'] = $product_prices[strval($offer -> {$cml['prices']} -> {$cml['price']} -> {$cml['price_id']})]['price'];
                    }
                } else {
                    $prices['base_price'] = 0;
                }
            }

            if (empty($prices)) {
                $prices['base_price'] = 0;
            }

            if ($amount < 0 && $negative_amount == 'N') {
                $amount = 0;
            }
            $o_amount = $amount;

            if (!empty($product_amount[$product_id])) {
                $o_amount = $o_amount + $product_amount[$product_id]['amount'];
            }

            $product_amount[$product_id]['amount'] = $o_amount;
            if (empty($combination_id)) {
        		if (isset($amount)) {
        			$product['amount'] = $amount;
        		}
                // [csmarket] limit bering
                if (Registry::get('runtime.company_id') == 29) {
                    unset($product['amount']);
                    $product['zero_price_action'] = 'P';
                }

                // [csmarket] limit pinta
                /* if (in_array(Registry::get('runtime.company_id'), array(41,46))) {
                    unset($product['amount']);
                }*/
		if (!empty($product))
                $this->db->query(
                    'UPDATE ?:products SET ?u WHERE product_id = ?i',
                    $product,
                    $product_id
                );

                $this->addProductPrice($product_id, $prices);
                $this->addMessageLog('Added product = ' . strval($offer -> {$cml['name']}) . ', price = ' . $prices['base_price'] . ' and amount = ' . $amount);

            } else {
                $product['tracking'] = 'O';
                $this->db->query(
                    'UPDATE ?:products SET ?u WHERE product_id = ?i',
                    $product,
                    $product_id
                );

                if ($schema_version == '2.07') {
                    $this->addProductPrice($product_id, array('base_price' => 0));
                    $option_id = $this->dataProductOption($product_id, $import_params['lang_code']);
                    $variant_id = $this->db->getField(
                        'SELECT variant_id FROM ?:product_option_variants WHERE external_id = ?s AND option_id = ?i',
                        $combination_id,
                        $option_id
                    );

                    if (!empty($option_id) && !empty($variant_id)) {
                        $price = ($this->s_commerceml['exim_1c_option_price'] == 'Y') ? '0.00' : $prices['base_price'];

                        $this->db->query('UPDATE ?:product_option_variants SET modifier = ?d WHERE variant_id = ?i', $price, $variant_id);
                        $add_options_combination = array($option_id => $variant_id);
                        $combination_hash = $this->addNewCombination($product_id, $combination_id, $add_options_combination, $import_params, $amount);
                        $this->addMessageLog('Added product = ' . strval($offer -> {$cml['name']}) . ', option_id = ' . $option_id . ', variant_id = ' . $variant_id . ', price = ' . $prices['base_price'] . ' and amount = ' . $amount);

                    } elseif (empty($variant_id) && $import_mode == 'global_option') {
                        $data_combination = $this->db->getRow(
                            'SELECT combination_hash, combination'
                            . ' FROM ?:product_options_inventory'
                            . ' WHERE external_id = ?s AND product_id = ?i',
                            $combination_id,
                            $product_id
                        );

                        $add_options_combination = empty($data_combination) ? array() : fn_get_product_options_by_combination($data_combination['combination']);
                        $this->addProductOptionException($add_options_combination, $product_id, $import_params, $amount);

                        if (!empty($data_combination['combination_hash'])) {
                            $image_pair_id = $this->db->getField('SELECT pair_id FROM ?:images_links WHERE object_id = ?i', $data_combination['combination_hash']);
                            $this->db->query('UPDATE ?:product_options_inventory SET amount = ?i WHERE combination_hash = ?s', $amount, $data_combination['combination_hash']);

                            if (!empty($image_pair_id)) {
                                $this->db->query('UPDATE ?:images_links SET object_id = ?i WHERE pair_id = ?i', $data_combination['combination_hash'], $image_pair_id);
                            }
                        }

                        $this->addMessageLog('Added global option product = ' . strval($offer -> {$cml['name']}) . ', price = ' . $prices['base_price'] . ' and amount = ' . $amount);

                    } elseif (empty($variant_id) && ($import_mode == 'individual_option' || $import_mode == 'same_option')) {
                        $data_combination = $this->db->getRow('SELECT combination_hash, combination FROM ?:product_options_inventory WHERE external_id = ?s AND product_id = ?i', $combination_id, $product_id);
                        $add_options_combination = fn_get_product_options_by_combination($data_combination['combination']);
                        $this->addProductOptionException($add_options_combination, $product_id, $import_params, $amount);

                        if (!empty($data_combination['combination_hash'])) {
                            $image_pair_id = $this->db->getField('SELECT pair_id FROM ?:images_links WHERE object_id = ?i', $data_combination['combination_hash']);
                            $this->db->query('UPDATE ?:product_options_inventory SET amount = ?i WHERE combination_hash = ?s', $amount, $data_combination['combination_hash']);

                            if (!empty($image_pair_id)) {
                                $this->db->query('UPDATE ?:images_links SET object_id = ?i WHERE pair_id = ?i', $data_combination['combination_hash'], $image_pair_id);
                            }
                        }

                        $this->addMessageLog('Added individual option product = ' . strval($offer -> {$cml['name']}) . ', price = ' . $prices['base_price'] . ' and amount = ' . $amount);
                    }
                } else {
                    $variant_data = array(
                        'amount' => $amount
                    );

                    if ($import_mode == 'standart') {
                        $this->addProductPrice($product_id, array('base_price' => 0));
                        $variant_data['price'] = $prices['base_price'];
                    }

                    if (!empty($product_amount[$product_id][$combination_id])) {
                        $amount = $amount + $product_amount[$product_id]['amount'];
                    }

                    if ($import_mode == 'variations') {
                        $amount = $product_amount[$product_id]['amount'];
                    }

                    $product_amount[$product_id]['amount'] = $amount;

                    $options = $this->addProductCombinations($offer, $product_id, $import_params, $combination_id, $variant_data);

                    if (!empty($options) && $import_mode == 'variations') {
                        $options['prices'] = $prices;
                        $options['amount'] = $variant_data['amount'];
                        $this->updateProductCombinations($offer, $product_id, $combination_id, $options, $import_params);
                    }

                    $this->addMessageLog('Added option product = ' . strval($offer -> {$cml['name']}) . ', price = ' . $prices['base_price'] . ' and amount = ' . $amount);
                }

                if ($this->s_commerceml['exim_1c_option_price'] == 'Y') {
                    $this->addProductPrice($product_id, $prices);
                }

                if (isset($offer -> {$cml['image']}) && isset($combination_hash)) {
                    $import_params['object_type'] = 'product_option';

                    foreach ($offer -> {$cml['image']} as $image) {
                        $filename = fn_basename(strval($image));
                        $this->addProductImage($filename, true, $combination_hash, $import_params);
                    }
                }
            }
            
            // [csmarket]
            if ($product_data['timestamp'] > time() - SECONDS_IN_DAY) {
                if (isset($prices['qty_prices']) && !empty($prices['qty_prices']) ) {
                    $ugroups = fn_array_column($prices['qty_prices'], 'usergroup_id');
                } else {
                    $ugroups = fn_get_usergroups(array('type' => 'C', 'status' => array('A', 'H')));
                    $ugroups = array_keys($ugroups);
                }
                if (!empty($product_data['product_id'])) {
                    $this->db->query('UPDATE ?:products SET usergroup_ids = ?s WHERE product_id = ?i', implode(',', $ugroups), $product_data['product_id']);
                }
            }

            $product['status'] = $this->updateProductStatus($product_id, $product_data, $product_amount[$product_id]['amount']);

            if ($import_params['service_exchange'] == '' && ($count_import_offers == COUNT_IMPORT_PRODUCT)) {
                fn_echo("imported: " . $count_import_offers . "\n");
                $count_import_offers = 0;
            }
            unset($amount);
        }

        if ($progress) {
            if (!isset(\Tygh::$app['session']['exim_1c'])) {
                \Tygh::$app['session']['exim_1c'] = array();
            }
            \Tygh::$app['session']['exim_1c']['import_offers'] = $offers_pos;
            fn_echo("processed: " . \Tygh::$app['session']['exim_1c']['import_offers'] . "\n");

            if ($import_params['manual']) {
                fn_redirect(Registry::get('config.current_url'));
            }
        } else {
            fn_echo("success\n");
            unset(\Tygh::$app['session']['exim_1c']['import_offers']);
            unset(\Tygh::$app['session']['exim_1c']['prices_commerseml']);
        }
    }

    public function dataOrderToFile($xml, $order_data, $lang_code)
    {
        $export_statuses = $this->s_commerceml['exim_1c_export_statuses'];
        $cml = $this->cml;

        $order_xml = $this->getOrderDataForXml($order_data, $cml);

        // univita currency exception
        if ($this->company_id == 1787) { 
		$order_xml[$cml['currency']] = '643';
		//unset($order_xml[$cml['currency']]);
        }

        if (empty($order_data['firstname'])) {
            unset($order_data['firstname']);
        }
        if (empty($order_data['lastname'])) {
            unset($order_data['lastname']);
        }
        if (empty($order_data['phone'])) {
            unset($order_data['phone']);
        }
        $order_data = fn_fill_contact_info_from_address($order_data);
        $order_xml[$cml['contractors']][$cml['contractor']] = $this->getDataOrderUser($order_data);

        if (!empty($order_data['fields'])) {
            $fields_export = $this->exportFieldsToFile($order_data['fields']);
        }

        if (!empty($fields_export)) {
            foreach ($fields_export as $field_export) {
                $order_xml[$cml['contractors']][$cml['contractor']][$field_export['description']] = $field_export['value'];
            }
        }

        $rate_discounts = 0;
        if (!empty($order_data['subtotal']) && (!empty($order_data['discount']) || !empty($order_data['subtotal_discount']))) {
            $o_subtotal = 0;

            if (!empty($order_data['discount'])) {
                foreach ($order_data['products'] as $product) {
                    $o_subtotal = $o_subtotal + $product['price'];
                }
            }

            if (empty($o_subtotal)) {
                $o_subtotal = $order_data['subtotal'] - $order_data['discount'];
            }

            if (($order_data['subtotal_discount'] > 0) && ($order_data['subtotal_discount'] < $o_subtotal)) {
                $rate_discounts = $order_data['subtotal_discount'] * 100 / $o_subtotal;

                $order_xml[$cml['discounts']][$cml['discount']] = array(
                    $cml['name'] => $cml['orders_discount'],
                    $cml['total'] => $order_data['subtotal_discount'],
                    $cml['rate_discounts'] => $rate_discounts,
                    $cml['in_total'] => 'true'
                );
            }
        }

        $order_xml[$cml['products']] = $this->dataOrderProducts($xml, $order_data, $rate_discounts);

        $data_status = fn_get_statuses('O', $order_data['status']);

        $status = (!empty($data_status)) ? $data_status[$order_data['status']]['description'] : $order_data['status'];

        if (empty($status)) {
            $status = 'O';
        }

        if ($export_statuses == 'Y') {
            $order_xml[$cml['value_fields']][][$cml['value_field']] = array(
                $cml['name'] => $cml['status_order'],
                $cml['value'] => $status
            );
        }

        list($payment, $shipping) = $this->getAdditionalOrderData($order_data);

        $order_xml[$cml['value_fields']][][$cml['value_field']] = array(
            $cml['name'] => $cml['payment'],
            $cml['value'] => $payment
        );

                    
        $order_xml[$cml['value_fields']][][$cml['value_field']] = array(
            $cml['name'] => $cml['shipping'],
            $cml['value'] => $shipping
        );

        fn_set_hook('exim1c_order_xml_pre', $order_xml, $order_data, $cml);

        $xml = $this->parseArrayToXml($xml, array($cml['document'] => $order_xml));

        return $xml;
    }

    public function getProductDataByLinkType($link_type, $_product, $cml)
    {
        list($guid_product, $combination_id) = $this->getProductIdByFile($_product -> {$cml['id']});

        $article = strval($_product -> {$cml['article']});
        $barcode = strval($_product -> {$cml['bar']});

        $product_data = array();
        $company_condition = fn_get_company_condition('company_id', true, '', false, true);

        if ($link_type == 'article') {
            $product_data = $this->db->getRow(
                "SELECT product_id, update_1c FROM ?:products WHERE product_code = ?s $company_condition",
                $article
            );

        } elseif ($link_type == 'barcode') {
            $product_data = $this->db->getRow(
                "SELECT product_id, update_1c FROM ?:products WHERE product_code = ?s $company_condition",
                $barcode
            );

        } else {
            $product_data = $this->db->getRow(
                "SELECT product_id, update_1c FROM ?:products WHERE external_id = ?s $company_condition",
                $guid_product
            );
            if (!$product_data) {
                if (!$product_data) {
                    if (isset($_product -> {$cml['value_fields']} -> {$cml['value_field']})) {
                        $requisites = $_product -> {$cml['value_fields']} -> {$cml['value_field']};
                        list($full_name, $product_code, $html_description) = $this->getAdditionalDataProduct($requisites, $cml);
                    }

                    $cond = $this->db->quote('pd.product = ?s', trim(strval($_product -> {$cml['name']})));
                    if (trim($full_name)) {
                        $cond = $this->db->quote("( $cond OR pd.product = ?s )", $full_name);
                    }

                    $product_data = $this->db->getRow(
                        "SELECT ?:products.product_id, update_1c FROM ?:products LEFT JOIN ?:product_descriptions as pd ON pd.product_id = ?:products.product_id AND pd.lang_code = ?s WHERE $cond $company_condition", DESCR_SL
                    );

                    if (empty($product_data) && !empty($article)) {
                        $product_data = $this->db->getRow(
                            "SELECT ?:products.product_id, update_1c FROM ?:products LEFT JOIN ?:product_descriptions as pd ON pd.product_id = ?:products.product_id AND pd.lang_code = ?s WHERE product_code = ?s $company_condition", DESCR_SL,
                            strval($_product -> {$cml['article']})
                        );
                    }
                }
            }
        }
        return $product_data;
    }

    public function addDataProductByFile($_product, $cml, $categories_commerceml, $import_params)
    {
        $allow_import_features = $this->s_commerceml['exim_1c_allow_import_features'];
        $add_tax = $this->s_commerceml['exim_1c_add_tax'];
        $schema_version = $this->s_commerceml['exim_1c_schema_version'];
        $link_type = $this->s_commerceml['exim_1c_import_type'];
        $log_message = "";

        if (empty($_product -> {$cml['name']})) {
            $log_message = "Name is not set for product with id: " . $_product -> {$cml['id']};

            return $log_message;
        }
        list($guid_product, $combination_id) = $this->getProductIdByFile($_product -> {$cml['id']});

        $product_data = $this->getProductDataByLinkType($link_type, $_product, $cml);

//             $pdata = fn_get_product_data($product_data['product_id']);
//             if (empty($pdata)) {
//                 fn_delete_product($product_data['product_id']);
//                 //fn_print_die($product_data['product_id']);
//             }

        $product_update = !empty($product_data['update_1c']) ? $product_data['update_1c'] : 'Y';
        $product_id = (!empty($product_data['product_id'])) ? $product_data['product_id'] : 0;

        $product_status = $_product->attributes()->{$cml['status']};
        if (!empty($product_status) && (string) $product_status == $cml['delete']) {
            if ($product_id != 0) {
                fn_delete_product($product_id);
                $log_message = "\n Deleted product: " . strval($_product -> {$cml['name']});
            }

            return $log_message;
        }

        if (!empty($_product -> {$cml['status']}) && strval($_product -> {$cml['status']}) == $cml['delete']) {
            if ($product_id != 0) {
                fn_delete_product($product_id);
                $log_message = "\n Deleted product: " . strval($_product -> {$cml['name']});
            }

            return $log_message;
        }

        if ($this->checkUploadProduct($product_id, $product_update)) {
            //$this->s_commerceml['exim_1c_allow_import_categories'] = 'N';
            $product = $this->dataProductFile($_product, $product_id, $guid_product, $categories_commerceml, $import_params);
            // [cs-market] default func adds default category to existing ones
            if (($key = array_search($this->s_commerceml['exim_1c_default_category'], $product['category_ids'])) !== false && count($product['category_ids']) > 1) {
                unset($product['category_ids'][$key]);
            }

            if ($product_id == 0) {
                $this->newDataProductFile($product, $import_params);
            }

            $this->db->query(
                'UPDATE ?:products SET company_id = ?i WHERE product_id = ?i',
                $this->company_id,
                $product_id
            );

            if ((isset($_product -> {$cml['properties_values']} -> {$cml['property_values']}) || isset($_product -> {$cml['manufacturer']})) && ($allow_import_features == 'Y') && (!empty($this->features_commerceml))) {
                $product = $this->dataProductFeatures($_product, $product, $import_params);
                if (!empty($this->features_commerceml['sticker'])) {
                    foreach ($_product -> {$cml['properties_values']} -> {$cml['property_values']} as $_feature) {
                        $feature_id = strval($_feature -> {$cml['id']});
                        if ($this->features_commerceml['sticker']['id'] != $feature_id) {
                            continue;
                        }
                        $p_feature_name = (string) $_feature->{$cml['value']};
                        if (!empty($this->features_commerceml['sticker']['variants'])) {
                            $p_feature_name = empty($this->features_commerceml['sticker']['variants'][$p_feature_name]['value'])
                                ? ''
                                : (string) $this->features_commerceml['sticker']['variants'][$p_feature_name]['value'];
                        }
                        if (is_callable('fn_get_stickers')) {
                            $stickers = fn_get_stickers(['name' => $p_feature_name]);
                            $product['sticker_ids'] = fn_array_column($stickers, 'sticker_id');
                        }
                    }
                }
            }

            if (isset($_product -> {$cml['value_fields']} -> {$cml['value_field']})) {
                $this->dataProductFields($_product, $product);
            }

            if (isset($_product -> {$cml['taxes_rates']}) && ($add_tax == 'Y')) {
                $product['tax_ids'] = $this->addProductTaxes($_product -> {$cml['taxes_rates']}, $product_id);
            }

            if ($this->company_id == '29') {
                $product['full_description'] = strval($_product -> {$cml['bar']});
            }

            // limit for pinta for Katerina
            if (in_array($this->company_id, array(41, 46)) && $product_id) {
                $_p = array(
                    'external_id' => $product['external_id'],
/*                    'product' => $product['product'],
                    'category_id' => $product['category_id'],
                    'category_ids' => $product['category_ids'],*/
                );
                $product = $_p;
            }

//	    if (!$product_id) {

			$product_id = fn_update_product($product, $product_id, $import_params['lang_code']);

			$log_message = "\n Added product: " . $product['product'] . " commerceml_id: " . strval($_product -> {$cml['id']});

			// Import product features
			if (!empty($product['features'])) {
			    $variants_data['product_id'] = $product_id;
			    $variants_data['lang_code'] = $import_params['lang_code'];
			    $variants_data['category_id'] = $product['category_id'];
			    $this->addProductFeatures($product['features'], $variants_data, $import_params);
			}

			// Import images
			$image_main = true;
			if (isset($_product -> {$cml['image']})) {
			    foreach ($_product -> {$cml['image']} as $image) {
				$filename = fn_basename(strval($image));
				$this->addProductImage($filename, $image_main, $product_id, $import_params);
				$image_main = false;
			    }
			}

			// Import combinations
			if (isset($_product -> {$cml['product_features']} -> {$cml['product_feature']}) && $schema_version == '2.07') {
			    $this->addProductCombinations($_product, $product_id, $import_params, $combination_id);
			}
//	    }
        }

        return $log_message;
    }

    /**
     * Prepares the array of user data for export to the accounting systems.
     *
     * @param $order_data The array with the order data.
     *
     * @return array The array with the user data.
     */
    public function getDataOrderUser($order_data)
    {
        $cml = $this->cml;
        $user_id = '0' . $order_data['order_id'];
        $unregistered = $cml['yes'];
        if (!empty($order_data['user_id'])) {
            $user_id = $order_data['user_id'];
            $unregistered = $cml['no'];
        }

        if (!isset($order_data['firstname'])) {
            $order_data['firstname'] = '';
        }

        if (!isset($order_data['lastname'])) {
            $order_data['lastname'] = '';
        }

        if (!isset($order_data['phone'])) {
            $order_data['phone'] = '-';
        }

        $name_company = trim(empty($order_data['company']) ? $order_data['lastname'] . ' ' . $order_data['firstname'] : $order_data['company']);

        $zipcode = $this->getContactInfoFromAddress($order_data, 'zipcode');
        $country = $this->getContactInfoFromAddress($order_data, 'country_descr');
        $city = $this->getContactInfoFromAddress($order_data, 'city');
        $address1 = $this->getContactInfoFromAddress($order_data, 'address');
        $address2 = $this->getContactInfoFromAddress($order_data, 'address_2');

        $user_xml = array(
            $cml['id'] => $user_id,
            $cml['unregistered'] => $unregistered,
            $cml['name'] => $name_company,
            $cml['role'] => $cml['seller'],
            $cml['full_name_contractor'] => trim($order_data['lastname'] . ' ' . $order_data['firstname']),
            $cml['lastname'] => $order_data['lastname'],
            $cml['firstname'] => $order_data['firstname']
        );
        if (!empty($order_data['profile_id'])) {
            $user_xml[$cml['profile']] = $order_data['profile_id'];
        }

        $user_xml[$cml['address']][$cml['presentation']] = "$zipcode, $country, $city, $address1 $address2";
        $user_xml[$cml['address']][][$cml['address_field']] = array(
            $cml['type'] => $cml['post_code'],
            $cml['value'] => $zipcode
        );
        $user_xml[$cml['address']][][$cml['address_field']] = array(
            $cml['type'] => $cml['country'],
            $cml['value'] => $country
        );
        $user_xml[$cml['address']][][$cml['address_field']] = array(
            $cml['type'] => $cml['city'],
            $cml['value'] => $city
        );
        $user_xml[$cml['address']][][$cml['address_field']] = array(
            $cml['type'] => $cml['address'],
            $cml['value'] => "$address1"
        );
        if (trim($address2)) {
        $user_xml[$cml['address']][][$cml['address_field']] = array(
            $cml['type'] => $cml['address']."2",
            $cml['value'] => "$address2"
        );
        }

        $phone = (!empty($order_data['phone'])) ? $order_data['phone'] : '-';
        $user_xml[$cml['contacts']][][$cml['contact']] = array(
            $cml['type'] => $cml['mail'],
            $cml['value'] => $order_data['email']
        );
        $user_xml[$cml['contacts']][][$cml['contact']] = array(
            $cml['type'] => $cml['work_phone'],
            $cml['value'] => $phone
        );
        return $user_xml;
    }

    public function importFileOrders($xml, $lang_code)
    {
        $cml = $this->cml;
        if (isset($xml->{$cml['document']})) {
            $orders_data = $xml->{$cml['document']};

            $statuses = array();
            $data_status = fn_get_statuses('O');
            if (!empty($data_status)) {
                foreach ($data_status as $status) {
                    $statuses[$status['description']] = array(
                        'status' => $status['status'],
                        'description' => $status['description']
                    );
                }
            }
            $link_type = $this->s_commerceml['exim_1c_import_type'];

            foreach ($orders_data as $order_data) {
                $import_id = strval($order_data->{$cml['id']});
                $order_id = strval($order_data->{$cml['number']});

                // [cs-market] update order products
                $order_info = fn_get_order_info($order_id);
                fn_clear_cart($cart);
                
                if (!empty($order_info['user_id'])) {
                    $_data = db_get_row("SELECT user_id, user_login as login FROM ?:users WHERE user_id = ?i", $order_info['user_id']);
                }
                $customer_auth = fn_fill_auth($_data, array(), false, 'C');

                fn_form_cart($order_id, $cart, $customer_auth);
                fn_store_shipping_rates($order_id, $cart, $customer_auth);
                $cart['order_id'] = $order_id;
                $cart['order_status'] = $statuses[strval($data_field->{$cml['value']})]['status'];
                $cart['products'] = array();
                
                foreach ($order_data->{$cml['products']}->{$cml['product']} as $xml_product) {
                    $product_data = $this->getProductDataByLinkType($link_type, $xml_product, $cml);
                    
                    $_item = array (
                        $product_data['product_id'] => array (
                            'amount' => strval($xml_product->{$cml['amount']}),
                            'price' => strval($xml_product->{$cml['price_per_item']}),
                            'stored_price' => 'Y',
                        ),
                    );
                    define('ORDER_MANAGEMENT', true);
                    fn_add_product_to_cart($_item, $cart, $customer_auth);
                }

                foreach ($order_data->{$cml['value_fields']}->{$cml['value_field']} as $data_field) {
                    if (!empty($order_id) && ($data_field->{$cml['name']} == $cml['status_order']) && (!empty($statuses[strval($data_field->{$cml['value']})]))) {
                        $new_status = $statuses[strval($data_field->{$cml['value']})]['status'];
                    }
                    // TODO move to settings
                    if ($data_field->{$cml['name']} == 'Дата отгрузки по 1С' && !empty(strtotime(strval($data_field->{$cml['value']})))) {
                        $cart['delivery_date'] = strtotime(strval($data_field->{$cml['value']}));
                    }
                }

                fn_calculate_cart_content($cart, $customer_auth);
                if (!fn_cart_is_empty($cart) && $order_info['company_id'] != 12) {
                    fn_place_order($cart, $customer_auth, 'save');
                }

                if ($new_status) $this->db->query("UPDATE ?:orders SET status = ?s WHERE order_id = ?i", $new_status, $order_id);

                fn_set_hook('exim_1c_update_order', $order_data, $cml);
            }
        }
    }
        /**
     * Creates an array with products prices.
     * Prices from the import file are added to the array when the name of the price matches
     * the name entered in the admin panel.
     *
     * @param object  $prices_file   The simplexml object with prices from the imported file.
     * @param array   $data_prices   The array with the names of price fields;
     *                               these names are entered in the admin panel.
     *
     * @return The array with the products prices.
     */
    public function getPricesDataFromFile($prices_file, $data_prices)
    {
        $cml = $this->cml;
        $prices_commerseml = array();
        foreach ($prices_file -> {$cml['price_type']} as $_price) {
            $found = false;
            foreach ($data_prices as $d_price) {
                if ($d_price['price_1c'] == strval($_price -> {$cml['name']})) {
                    $d_price['external_id'] = strval($_price -> {$cml['id']});
                    $prices_commerseml[] = $d_price;
                    $found = true;
                }
            }
            if (!$found) {
                //$name = trim(str_replace('1Ц', '', );
                $like_name = '%' . strval($_price -> {$cml['name']}) . '%';
                $user_id = 0;
                //$user_id = db_get_field('SELECT user_id FROM ?:users WHERE firstname LIKE ?l OR lastname LIKE ?l OR email LIKE ?l OR user_login LIKE ?l', $like_name, $like_name, $like_name, $like_name);
                //if (!$user_id) {
                    list($users, ) = fn_get_users(array('search_query' => strval($_price -> {$cml['name']}), 'user_type' => 'C', 'extended_search' => false), $_SESSION['auth'], 10);
                    if (!empty($users)) {
                        $user_id = reset($users)['user_id'];
                    }
                //}
                if ($user_id) {
                
                    $user = reset($users);
                    $prices_commerseml[] = array(
                        'price_1c' => strval($_price -> {$cml['name']}),
                        'type' => 'user_price',
                        'user_id' => $user_id,
                        'external_id' => strval($_price -> {$cml['id']}),
                    );
                }
            }
        }

        Tygh::$app['session']['exim_1c']['prices_commerseml'] = $prices_commerseml;

        return $prices_commerseml;
    }

    public function addProductPrice($product_id, $prices)
    {
        // List price updating
        if (isset($prices['list_price'])) {
            $this->db->query(
                'UPDATE ?:products SET list_price = ?d WHERE product_id = ?i',
                $prices['list_price'],
                $product_id
            );
        }

        // Prices updating
        $fake_product_data = array(
            'price' => isset($prices['base_price']) ? $prices['base_price'] : 0,
            'prices' => array(),
        );

        if (isset($prices['qty_prices'])) {
            $qty_prices[] = array(
                'price' => isset($prices['base_price']) ? $prices['base_price'] : 0,
                'usergroup_id' => 0
            );
            $prices['qty_prices'] = array_merge($qty_prices, $prices['qty_prices']);

            foreach ($prices['qty_prices'] as $qty_price) {
                $fake_product_data['prices'][] = array(
                    'product_id' => $product_id,
                    'price' => $qty_price['price'],
                    'lower_limit' => 1,
                    'usergroup_id' => $qty_price['usergroup_id']
                );
            }
        }
        if (!empty($prices['user_price']) && is_callable('fn_update_product_user_price')) {
            fn_update_product_user_price($product_id, $prices['user_price']);
        }
        fn_update_product_prices($product_id, $fake_product_data);

        if (fn_ult_is_shared_product($product_id) == 'Y') {
            fn_update_product_prices($product_id, $fake_product_data, $this->company_id);
        }
    }

    // send price only to limited products
    public function dataOrderProducts($xml, $order_data, $discount = 0)
    {
        $cml = $this->cml;
        $export_options = $this->s_commerceml['exim_1c_product_options'];

        $add_tax = $this->s_commerceml['exim_1c_add_tax'];
        if (!empty($order_data['taxes']) && $add_tax == 'Y') {
            $data_taxes = $this->dataOrderTaxs($order_data['taxes']);
        }

        if ($this->s_commerceml['exim_1c_order_shipping'] == 'Y' && $order_data['shipping_cost'] > 0) {
            $data_product = array(
                $cml['id'] => 'ORDER_DELIVERY',
                $cml['name'] => $cml['delivery_order'],
                $cml['price_per_item'] => $order_data['shipping_cost'],
                $cml['amount'] => 1,
                $cml['total'] => $order_data['shipping_cost'],
                $cml['multiply'] => 1,
            );
            $data_product[$cml['base_unit']]['attribute'] = array(
                $cml['code'] => '796',
                $cml['full_name_unit'] => $cml['item'],
                'text' => $cml['item']
            );
            $data_product[$cml['value_fields']][][$cml['value_field']] = array(
                $cml['name'] => $cml['spec_nomenclature'],
                $cml['value'] => $cml['service']
            );
            $data_product[$cml['value_fields']][][$cml['value_field']] = array(
                $cml['name'] => $cml['type_nomenclature'],
                $cml['value'] => $cml['service']
            );

            $data_products[][$cml['product']] = $data_product;
        }

        if (!empty($order_data['payment_surcharge']) && $order_data['payment_surcharge'] > 0) {
            $data_product = array(
                $cml['id'] => 'Payment_surcharge',
                $cml['name'] => $cml['payment_surcharge'],
                $cml['price_per_item'] => $order_data['payment_surcharge'],
                $cml['amount'] => 1,
                $cml['total'] => $order_data['payment_surcharge'],
                $cml['multiply'] => 1,
            );
            $data_product[$cml['base_unit']]['attribute'] = array(
                $cml['code'] => '796',
                $cml['full_name_unit'] => $cml['item'],
                'text' => $cml['item']
            );
            $data_product[$cml['value_fields']][][$cml['value_field']] = array(
                $cml['name'] => $cml['spec_nomenclature'],
                $cml['value'] => $cml['service']
            );
            $data_product[$cml['value_fields']][][$cml['value_field']] = array(
                $cml['name'] => $cml['type_nomenclature'],
                $cml['value'] => $cml['service']
            );

            $data_products[][$cml['product']] = $data_product;
        }

        // [cs-market] send price only to limited products
        $send_price_1c = db_get_hash_single_array('SELECT product_id, send_price_1c FROM ?:products WHERE product_id IN (?a)',  array('product_id', 'send_price_1c'),  fn_array_column($order_data['products'], 'product_id'));

        foreach ($order_data['products'] as $product) {
            $product_discount = 0;
            $product_subtotal = $product['subtotal'];
            $external_id = $this->db->getField("SELECT external_id FROM ?:products WHERE product_id = ?i", $product['product_id']);
            $external_id = (!empty($external_id)) ? $external_id : $product['product_id'];
            $product_name = $product['product'];
            if (!empty($product['product_options']) && $export_options == 'Y') {
                $this->setDataProductByOptions($product['product_id'], $product['product_options'], $external_id, $product_name);
            }

            $data_product = array(
                $cml['id'] => $external_id,
                $cml['code'] => $product['product_id'],
                $cml['article'] => $product['product_code'],
                $cml['name'] => $product_name,
                $cml['price_per_item'] => $product['base_price'],
                $cml['amount'] => $product['amount'],
                $cml['multiply'] => 1
            );

            $data_product[$cml['base_unit']]['attribute'] = array(
                $cml['code'] => '796',
                $cml['full_name_unit'] => $cml['item'],
                'text' => $cml['item']
            );

            if (!empty($discount)) {
                $p_subtotal = $product['price'] * $product['amount'];
                $product_discount = $p_subtotal * $discount / 100;

                if ($p_subtotal > $product_discount) {
                    $data_product[$cml['discounts']][][$cml['discount']] = array(
                        $cml['name'] => $cml['product_discount'],
                        $cml['total'] => $product_discount,
                        $cml['in_total'] => 'false',
                        $cml['rate_discounts'] => $discount,
                    );
                }
            }

            if(isset($product['discount'])) {
                $data_product[$cml['discounts']][][$cml['discount']] = array(
                    $cml['name'] => $cml['product_discount'],
                    $cml['total'] => $product['discount'],
                    $cml['in_total'] => 'true',
                    $cml['rate_discounts'] => round($product['discount'] / $product['base_price'] * 100),
                );
            }

            if (!empty($data_taxes['products'][$product['item_id']])) {
                $tax_value = 0;
                $subtotal = $product['subtotal'] - $product_discount;
                foreach ($data_taxes['products'][$product['item_id']] as $product_tax) {
                    $data_product[$cml['taxes_rates']][][$cml['tax_rate']] = array(
                        $cml['name'] => $product_tax['name'],
                        $cml['rate_t'] => $product_tax['value']
                    );

                    if ($product_tax['tax_in_total'] == 'false') {
                        $tax_value = $tax_value + ($subtotal * $product_tax['rate_value'] / 100);
                    }
                }

                $product_subtotal = $product['subtotal'] + $tax_value;
            }
            $data_product[$cml['total']] = $product_subtotal;
            $data_product[$cml['value_fields']][][$cml['value_field']] = array(
                $cml['name'] => $cml['spec_nomenclature'],
                $cml['value'] => $cml['product']
            );
            $data_product[$cml['value_fields']][][$cml['value_field']] = array(
                $cml['name'] => $cml['type_nomenclature'],
                $cml['value'] => $cml['product']
            );
            // [cs-market] send price only to limited products
            if ($send_price_1c[$product['product_id']] != 'Y') {
                unset($data_product[$cml['price_per_item']], $data_product[$cml['total']]);
            }
            $data_products[][$cml['product']] = $data_product;
        }

        return $data_products;
    }

    public function addProductFeatures($data_features, $variants_data, $import_params)
    {
        foreach ($data_features as $p_feature) {
            $variant_feature = array_merge($p_feature, $variants_data);

            if (!empty($variants_data['category_id'])) {
                $feature_categories = fn_explode(',', $this->db->getField("SELECT categories_path FROM ?:product_features WHERE feature_id = ?i", $p_feature['feature_id']));
                // avoid category addition
                /* if (!in_array($variants_data['category_id'], $feature_categories)) {
                    $feature_categories[] = $variants_data['category_id'];
                    $feature_categories = array_diff($feature_categories, array(''));
                    $this->db->query("UPDATE ?:product_features SET categories_path = ?s WHERE feature_id = ?i", implode(',', $feature_categories), $p_feature['feature_id']);
                }*/
            }

            $this->addFeatureValues($variant_feature);
        }
    }

    public function importFeaturesFile($data_features, $import_params, $data_pos_start, &$import_pos, &$progress)
    {
        $cml = $this->cml;
        $features_import = array();
        if (isset($data_features -> {$cml['property']})) {
            $promo_text = trim($this->s_commerceml['exim_1c_property_product']);
            $shipping_params = $this->getShippingFeatures();
            $features_list = fn_explode("\n", $this->s_commerceml['exim_1c_features_list']);
            $deny_or_allow_list = $this->s_commerceml['exim_1c_deny_or_allow'];
            $company_id = $this->company_id;
            foreach ($data_features -> {$cml['property']} as $_feature) {
                if ($import_params['service_exchange'] == '') {
                    $import_pos++;

                    if ($import_pos % COUNT_IMPORT_PRODUCT == 0) {
                        fn_echo('imported: ' . COUNT_IMPORT_PRODUCT . "\n");
                    }

                    if ($import_pos < $data_pos_start) {
                        continue;
                    }

                    if (\Tygh::$app['session']['exim_1c']['f_count_imports'] >= COUNT_1C_IMPORT) {
                        $progress = true;
                        break;
                    }
                    \Tygh::$app['session']['exim_1c']['f_count_imports']++;
                }

                $_variants = array();
                $feature_data = array();
                $feature_name = strval($_feature -> {$cml['name']});
                if ($feature_name == $cml['sticker']) {
                    $features_import['sticker']['id'] = strval($_feature -> {$cml['id']});
                    $features_import['sticker']['name'] = $cml['sticker'];
                    if (!empty($_feature -> {$cml['variants_values']})) {
                        $_feature_data = $_feature -> {$cml['variants_values']} -> {$cml['directory']};
                        foreach ($_feature_data as $_variant) {
                            $_variants[strval($_variant -> {$cml['id_value']})]['id'] = strval($_variant -> {$cml['id_value']});
                            $_variants[strval($_variant -> {$cml['id_value']})]['value'] = strval($_variant -> {$cml['value']});
                            $f_variants[strval($_variant -> {$cml['id_value']})]['external_id'] = strval($_variant -> {$cml['id_value']});
                            $f_variants[strval($_variant -> {$cml['id_value']})]['variant'] = strval($_variant -> {$cml['value']});
                        }
                    }
                    $features_import['sticker']['variants'] = $_variants;
                }

                if ($deny_or_allow_list == 'do_not_import') {
                    if (in_array($feature_name, $features_list)) {
                        $this->addMessageLog("Feature is not added (do not import): " . $feature_name);
                        continue;
                    }
                } elseif ($deny_or_allow_list == 'import_only') {
                    if (!in_array($feature_name, $features_list)) {
                        $this->addMessageLog("Feature is not added (import only): " . $feature_name);
                        continue;
                    }
                }

                $feature_id = $this->db->getField("SELECT feature_id FROM ?:product_features WHERE external_id = ?s", strval($_feature -> {$cml['id']}));
                $new_feature = false;

                if (empty($feature_id)) {
                    $new_feature = true;
                    $feature_id = 0;
                }

                $f_variants = array();
                if (!empty($_feature -> {$cml['variants_values']})) {
                    $_feature_data = $_feature -> {$cml['variants_values']} -> {$cml['directory']};
                    foreach ($_feature_data as $_variant) {
                        $_variants[strval($_variant -> {$cml['id_value']})]['id'] = strval($_variant -> {$cml['id_value']});
                        $_variants[strval($_variant -> {$cml['id_value']})]['value'] = strval($_variant -> {$cml['value']});
                        $f_variants[strval($_variant -> {$cml['id_value']})]['external_id'] = strval($_variant -> {$cml['id_value']});
                        $f_variants[strval($_variant -> {$cml['id_value']})]['variant'] = strval($_variant -> {$cml['value']});
                    }
                }

                $feature_data = $this->dataFeatures($feature_name, $feature_id, strval($_feature -> {$cml['type_field']}), $this->s_commerceml['exim_1c_used_brand'], $this->s_commerceml['exim_1c_property_for_manufacturer'], strval($_feature -> {$cml['id']}));

                if ($this->displayFeatures($feature_name, $shipping_params)) {
                    if ($promo_text != $feature_name) {

                        if (!empty($f_variants)) {
                            $feature_data['variants'] = $f_variants;
                        }

                        $feature_id = fn_update_product_feature($feature_data, $feature_id);
                        $this->addMessageLog("Feature is added: " . $feature_name);

                        // [csmarket] mve compatibility changes!!
                        if ($new_feature && !fn_allowed_for('MULTIVENDOR')) {
                            $this->db->query("INSERT INTO ?:ult_objects_sharing VALUES ($company_id, $feature_id, 'product_features')");
                        }
                    } else {
                        fn_delete_feature($feature_id);
                        $feature_id = 0;
                    }
                } else {
                    fn_delete_feature($feature_id);
                    $feature_id = 0;
                }
                $features_import[strval($_feature -> {$cml['id']})]['id'] = $feature_id;
                $features_import[strval($_feature -> {$cml['id']})]['name'] = $feature_name;
                $features_import[strval($_feature -> {$cml['id']})]['type'] = $feature_data['feature_type'];

                if (!empty($_variants)) {
                    $features_import[strval($_feature -> {$cml['id']})]['variants'] = $_variants;
                }
            }
        }

        $feature_data = array();
        if ($this->s_commerceml['exim_1c_used_brand'] == 'field_brand') {
            $company_id = $this->company_id;
            $feature_id = $this->db->getField("SELECT feature_id FROM ?:product_features WHERE external_id = ?s AND company_id = ?i", "brand1c", $company_id);
            $new_feature = false;

            if (empty($feature_id)) {
                $new_feature = true;
                $feature_id = 0;
            }

            $feature_data = $this->dataFeatures($cml['brand'], $feature_id, ProductFeatures::EXTENDED, $this->s_commerceml['exim_1c_used_brand'], $this->s_commerceml['exim_1c_property_for_manufacturer'], "brand1c");
            $_feature_id = fn_update_product_feature($feature_data, $feature_id);
            $this->addMessageLog("Feature brand is added");

            if ($feature_id == 0) {
                $this->db->query("INSERT INTO ?:ult_objects_sharing VALUES ($company_id, $_feature_id, 'product_features')");
            }

            $features_import['brand1c']['id'] = (!empty($feature_id)) ? $feature_id : $_feature_id;
            $features_import['brand1c']['name'] = $cml['brand'];
        }

        if (!empty($features_import)) {
            if (!empty($this->features_commerceml)) {
                $_features_commerceml = $this->features_commerceml;
                $this->features_commerceml = fn_array_merge($_features_commerceml, $features_import);
            } else {
                $this->features_commerceml = $features_import;
            }
        }

        if (!empty($this->features_commerceml)) {
            \Tygh::$app['session']['exim_1c']['features_commerceml'] = $this->features_commerceml;
        }

        if ($import_params['service_exchange'] == '') {
            if (\Tygh::$app['session']['exim_1c']['f_count_imports'] + 1 >= COUNT_1C_IMPORT) {
                $progress = true;
            }
        } else {
            \Tygh::$app['session']['exim_1c']['f_count_imports'] = count($data_features -> {$cml['property']});
        }
    }

    public function checkParameterFileUpload()
    {
        $message = "";
        $log_message = "";

        if ($this->s_commerceml['status'] != 'A') {
            $message = "Addon Commerceml disabled";
        }

        if (!empty($_SERVER['PHP_AUTH_USER'])) {
            $_data['user_login'] = $_SERVER['PHP_AUTH_USER'];

            list($status, $user_data, $user_login, $password, $salt) = fn_auth_routines($_data, array());

            $this->import_params['user_data'] = $user_data;

            if (empty($user_data) || empty($user_data['password']) || $user_data['password'] != fn_generate_salted_password($_SERVER['PHP_AUTH_PW'], $salt)) {
                $message = "\n Error in login or password user";
            }

            if (!$this->checkPatternPermissionsCommerceml($user_data)) {
                $message = "\n Privileges for user not setted";
            }

            $log_message = $this->getCompanyStore($user_data);

        } else {
            $message = "\n Enter login and password user";
        }

        if (!empty($message) || !empty($log_message)) {
            $this->showMessageError($message);
            $this->addMessageLog($log_message);

            return true;
        }

        return false;
    }


    public function dataProductFields($data_product, &$product)
    {
        $cml = $this->cml;

        if (!empty($data_product -> {$cml['value_fields']} -> {$cml['value_field']})) {
            foreach ($data_product -> {$cml['value_fields']} -> {$cml['value_field']} as $value_field) {
                $_name_field = strval($value_field -> {$cml['name']});
                $_v_field = strval($value_field -> {$cml['value']});


                if (!empty($_v_field)) {
                    $product_params = $this->dataShippingParams($_v_field, $_name_field);

                    if (!empty($product_params)) {
                        $product = array_merge($product, $product_params);
                    }
                }
                // TODO move string to add-on settings
                if (in_array($_name_field, array('КвантЗаказа'))) {
                    $product['qty_step'] = (float) $_v_field;
                }
            }
        }
    }
}
