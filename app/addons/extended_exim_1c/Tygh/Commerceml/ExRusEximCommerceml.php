<?php

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
use Tygh\Addons\ProductVariations\ServiceProvider as VariationsServiceProvider;

class ExRusEximCommerceml extends RusEximCommerceml 
{
    public $prices_commerseml = array();
    public $data_prices = array();
    public $storages_map = array();
    
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
                    //$category_id = $this->db->getField("SELECT category_id FROM ?:categories WHERE category_id IN (?a) AND company_id = ?i", $category_ids, $this->company_id);
                    $category_id = reset($category_ids);
                }

                if (empty($category_id)) {
                    $this->addMessageLog("New category: " . strval($_group -> {$this->cml['name']}));
                }

                $category_data = $this->getDataCategoryByFile($_group, $category_id, $parent_id, $import_params['lang_code']);

                if ($import_params['user_data']['user_type'] != 'V' && !Registry::get('runtime.company_id')) {
                    $category_id = fn_update_category($category_data, $category_id, $import_params['lang_code']);
                    $this->addMessageLog("Add category: " . $category_data['category']);
                } elseif (empty($category_id)) {
                    $category_id = $default_category;
                    // [csmarket] get extra categories
                    list($categories) = fn_get_categories(['search_query' => strval($_group -> {$cml['name']}), 'total_items' => 1], $import_params['lang_code']);
                    if (!empty($categories)) {
                        $categories = array_keys($categories);
                        $category_id = reset($categories);
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
        $prices = [
            'base_price' => 0,
            'qty_prices' => []
        ];
        $list_prices = array();
        foreach ($product_prices as $external_id => $p_price) {
            foreach ($prices_commerseml as $p_commerseml) {
                if (!empty($p_commerseml['external_id'])) {
                    if ($external_id == $p_commerseml['external_id'] || (isset($p_price['external_id']) && $p_commerseml['external_id'] == $p_price['external_id'])) {
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
                                'user_id' => $p_commerseml['user_id'],
                                'storage_id' => $p_price['storage_id'],
                            );
                        }

                        if (isset($p_commerseml['usergroup_id']) && $p_commerseml['usergroup_id'] > 0) {
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

        if (empty($prices['qty_prices'])) unset($prices['qty_prices']);

        return $prices;
    }

    public function importStoragesFromOffersFile($xml)
    {
        $cml = $this->cml;

        if (!isset($xml->{$cml['warehouses']}->{$cml['warehouse']})) {
            return;
        }

        foreach ($xml->{$cml['warehouses']}->{$cml['warehouse']} as $warehouse_xml_data) {
            $this->getStorageByXml($warehouse_xml_data);
        }
    }

    protected function getStorageByXml($warehouse_xml_data)
    {
        $cml = $this->cml;
        $id = strval($warehouse_xml_data->{$cml['id']});
        $this->findStorageId($id);
    }

    protected function findStorageId($uid)
    {
        if (isset($this->storages_map[$uid])) {
            return $this->storages_map[$uid];
        }

        if ($storage_id = (int) $this->db->getField('SELECT storage_id FROM ?:storages WHERE code = ?s', $uid)) {
            return $this->storages_map[$uid] = $storage_id;
        }

        return null;
    }

    public function importProductOffersFile($data_offers, $import_params)
    {
        $cml = $this->cml;
        $params = [
            'create_prices'         => $this->s_commerceml['exim_1c_create_prices'],
            'allow_negative_amount' => Registry::get('settings.General.allow_negative_amount'),
            'all_currencies'        => $this->dataProductCurrencies(),
            'price_offers'          => [],
            'prices_commerseml'     => [],
        ];

        $prices_commerseml = &$this->prices_commerseml;
        if (!empty(\Tygh::$app['session']['exim_1c']['prices_commerseml'])) {
            $prices_commerseml = \Tygh::$app['session']['exim_1c']['prices_commerseml'];
        }
        

        $this->importWarehousesFromOffersFile($data_offers, $import_params);
        if (Registry::get('addons.storages.status') == 'A') $this->importStoragesFromOffersFile($data_offers, $import_params);

        if (isset($data_offers -> {$cml['prices_types']} -> {$cml['price_type']})) {
            $params['price_offers'] = $this->dataPriceOffers($data_offers -> {$cml['prices_types']});


            if ($params['create_prices'] == 'Y') {
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
                $params['prices_commerseml'] = $prices_commerseml;
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
        $last_product_guid = null;
        $last_product_offers = [];
        foreach ($data_offers -> {$cml['offers']} -> {$cml['offer']} as $offer) {
            $offers_pos++;

            if ($offers_pos < $offer_pos_start) {
                continue;
            }

            if ($offers_pos - $offer_pos_start + 1 > COUNT_1C_IMPORT && $import_params['service_exchange'] == '') {
                $progress = true;
                break;
            }

            list($product_guid, $combination_id) = $this->getProductIdByFile(strval($offer -> {$cml['id']}));

            if ($last_product_guid && $product_guid !== $last_product_guid) {
                $count_import_offers += $this->importProductOffers($last_product_guid, $last_product_offers, $params, $import_params);
                $last_product_offers = [];
            }

            $last_product_offers[$combination_id] = $offer;
            $last_product_guid = $product_guid;

            if ($import_params['service_exchange'] == '' && ($count_import_offers == COUNT_IMPORT_PRODUCT)) {
                fn_echo("imported: " . $count_import_offers . "\n");
                $count_import_offers = 0;
            }
        }

        if ($last_product_offers) {
            $count_import_offers += $this->importProductOffers($last_product_guid, $last_product_offers, $params, $import_params);

            if ($import_params['service_exchange'] == '' && ($count_import_offers == COUNT_IMPORT_PRODUCT)) {
                fn_echo("imported: " . $count_import_offers . "\n");
            }
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

    protected function convertOfferStoragesStockXmlToOfferStorages($offer)
    {
        $cml = $this->cml;
        $result = [];

        /** @var \SimpleXMLElement $warehouse_info */
        foreach ($offer->{$cml['warehouse']} as $storage_info) {

            $storage = $storage_info->attributes();

            $storage_id = $this->findStorageId((string) $storage->{$cml['warehouse_id']});

            if ($storage_id === null) {
                continue;
            }

            $result[$storage_id] = (int) $storage->{$cml['warehouse_in_stock']};
            if ($result[$storage_id] < 0) $result[$storage_id] = 0;
        }

        return $result;
    }

    protected function importProductStoragesStock($product_id, $offer, $storages_amounts)
    {
        $cml = $this->cml;

        if (!isset($offer->{$cml['warehouse']}) || empty($product_id)) {
            return;
        }

        $storages_amounts = $this->convertOfferStoragesStockXmlToOfferStorages($offer);

        if ($storages_amounts) {
            $pdata = $this->db->getRow('SELECT min_qty, qty_step FROM ?:products WHERE product_id = ?i', $product_id);

            foreach ($storages_amounts as $storage_id => $amount) {
                $u_data[] = [
                    'storage_id' => $storage_id,
                    'product_id' => $product_id,
                    'amount' => $amount,
                    'min_qty' => $pdata['min_qty'],
                    'qty_step' => $pdata['qty_step'],
                ];
            }
            $this->db->query('DELETE FROM ?:storages_products WHERE product_id = ?i', $product_id);
            $this->db->query('REPLACE INTO ?:storages_products ?m', $u_data);
        }
    }

    protected function importProductOffersAsOptions($product_guid, array $offers, array $params, array $import_params)
    {
        $count_import_offers = 0;
        $product_amount = $product = [];
        $cml = $this->cml;
        $link_type = $this->s_commerceml['exim_1c_import_type'];

        // [csmarket]
        $company_condition = fn_get_company_condition('company_id', true, '', false, true);
        if ($product_guid) $product_data = $this->db->getRow("SELECT product_id, update_1c, status, tracking, product_code, timestamp FROM ?:products WHERE external_id = ?s $company_condition", $product_guid);

        if (empty($product_data)) {
            $_product_data = $this->getProductDataByLinkType($link_type, reset($offers), $cml);
            if (!empty($_product_data))
            $product_data = $this->db->getRow("SELECT product_id, update_1c, status, tracking, product_code, timestamp FROM ?:products WHERE product_id = ?s $company_condition", $_product_data['product_id']);
        }

        $product_id = empty($product_data['product_id']) ? 0 : $product_data['product_id'];

        if (empty($product_id)) {
            return;
        }

        $product_data = fn_normalize_product_overridable_fields($product_data);

        $storages_amounts = $warehouses_amounts = [];

        foreach ($offers as $combination_id => $offer) {
            if (!$this->checkImportPrices($product_data)) {
                continue;
            }

            $product_code = $this->getProductCodeByOffer($offer);

            $amount = $this->getProductAmountByOffer($offer, $params);

            if ($product_code) {
                $product['product_code'] = $product_code;
            }

            $prices = $this->getProductPricesByOffer($offer, $params);

            $count_import_offers++;

            if ($amount !== false) {
                if (!empty($product_amount[$product_id])) {
                    $amount = $amount + $product_amount[$product_id]['amount'];
                }

                $product_amount[$product_id]['amount'] = $amount;
                $product['amount'] = $amount;
            }

            $warehouses_amounts = $this->importProductWarehousesStock($product_id, $offer, $warehouses_amounts);
            if (Registry::get('addons.storages.status') == 'A') $storages_amounts = $this->importProductStoragesStock($product_id, $offer, $storages_amounts);

            $this->addProductPrice($product_id, $prices);
            $this->addProductXmlFeaturesAsOptions($offer, $product_id, $import_params, $combination_id, [], isset($product['product_code']) ? $product['product_code'] : false);
            $this->addMessageLog('Added product = ' . strval($offer -> {$cml['name']}) . ', price = ' . $prices['base_price']);
        }

        $this->db->query('UPDATE ?:products SET ?u WHERE product_id = ?i', $product, $product_id);
        $this->sendProductStockNotifications($product_id, $product['amount']);
            
        // [csmarket]
        if ($product_data['timestamp'] > time() - SECONDS_IN_DAY) {
            if (isset($prices['qty_prices']) && !empty($prices['qty_prices']) ) {
                $ugroups = fn_array_column($prices['qty_prices'], 'usergroup_id');
            } else {
                $ugroups = fn_get_usergroups(array('type' => 'C', 'status' => array('A', 'H')));
                $ugroups = array_keys($ugroups);
            }
            if (!empty($product_data['product_id']) && !empty($ugroups)) {
                $this->db->query('UPDATE ?:products SET usergroup_ids = ?s WHERE product_id = ?i', implode(',', $ugroups), $product_data['product_id']);
            }
        }

        $product['status'] = $this->updateProductStatus($product_id, $product_data, $product_amount[$product_id]['amount']);

        return $count_import_offers;
    }

    protected function getProductAmountByOffer($xml_offer, $params)
    {
        $cml = $this->cml;
        if (!isset($xml_offer -> {$cml['amount']}) && !isset($xml_offer -> {$cml['warehouse']})) {
            return false;
        }

        $allow_negative_amount = $params['allow_negative_amount'];

        if (isset($xml_offer -> {$cml['amount']})) {
            $amount = (int) $xml_offer -> {$cml['amount']};
        } elseif (isset($xml_offer -> {$cml['warehouse']})) {
            foreach ($xml_offer -> {$cml['warehouse']} as $warehouse) {
                $amount += (int) $warehouse[$cml['warehouse_in_stock']];
            }
        }

        if (isset($amount) && $amount < 0 && $allow_negative_amount == 'N') {
            $amount = 0;
        }

        return isset($amount) ? $amount : false;
    }

    public function conversionProductPrices($p_prices, $price_offers)
    {
        $cml = $this->cml;
        $product_prices = array();

        if (!empty($p_prices) && !empty($price_offers)) {
            foreach ($p_prices as $p_price) {
                $price = strval($p_price -> {$cml['price_per_item']});
                $external_id = $key = strval($p_price -> {$cml['price_id']});
                if (!empty($this->storages_map[strval($p_price -> {$cml['warehouse_id']})])) $key .= $this->storages_map[strval($p_price -> {$cml['warehouse_id']})];
                if (!empty($price_offers[$external_id]['coefficient'])) {
                    $price = $price * $price_offers[$external_id]['coefficient'];
                }

                $product_prices[$key] = array(
                    'price' => $price,
                    'storage_id' => 0,
                    'external_id' => $external_id,
                );

                if (!empty(strval($p_price -> {$cml['warehouse_id']})) && !empty($this->storages_map[strval($p_price -> {$cml['warehouse_id']})])) {
                    $product_prices[$key]['storage_id'] = $this->storages_map[strval($p_price -> {$cml['warehouse_id']})];
                }
            }
        }
        
        return $product_prices;
    }

    protected function getProductPricesByOffer($xml_offer, $params)
    {
        $cml = $this->cml;
        $prices = [
            'base_price' => 0
        ];

        if (!isset($xml_offer -> {$cml['prices']})) {
            return $prices;
        }

        $price_offers = $params['price_offers'];
        $all_currencies = $params['all_currencies'];
        $prices_commerseml = $params['prices_commerseml'];
        $create_prices = $params['create_prices'];

        if (isset($xml_offer -> {$cml['prices']}) && !empty($price_offers)) {
            $_price_offers = $price_offers;

            foreach ($xml_offer -> {$cml['prices']} -> {$cml['price']} as $c_price) {
                if (!empty($c_price -> {$cml['currency']})
                    && !empty($_price_offers[strval($c_price -> {$cml['price_id']})]['coefficient'])
                    && !empty($all_currencies[strval($c_price -> {$cml['currency']})]['coefficient'])
                ) {
                    $_price_offers[strval($c_price -> {$cml['price_id']})]['coefficient'] = $all_currencies[strval($c_price -> {$cml['currency']})]['coefficient'];
                }
            }

            $product_prices = $this->conversionProductPrices($xml_offer -> {$cml['prices']} -> {$cml['price']}, $_price_offers);

            if ($create_prices == 'Y') {
                $prices = $this->dataProductPrice($product_prices, $prices_commerseml);

                if (empty($prices) && (!empty($product_prices[strval($offer -> {$cml['prices']} -> {$cml['price']} -> {$cml['price_id']})]['price']))) {
                    $prices['base_price'] = $product_prices[strval($xml_offer -> {$cml['prices']} -> {$cml['price']} -> {$cml['price_id']})]['price'];
                }
            }
        }

        return $prices;
    }

    public function dataOrderToFile($xml, $order_data, $lang_code)
    {
        $export_statuses = $this->s_commerceml['exim_1c_export_statuses'];
        $cml = $this->cml;

        $order_xml = $this->getOrderDataForXml($order_data, $cml);

        $this->data_prices = $this->db->getHash(
            'SELECT price_1c, type, usergroup_id FROM ?:rus_exim_1c_prices WHERE company_id = ?i',
            'usergroup_id',
            $this->company_id
        );

        // univita currency exception
        if ($this->company_id == 1787) { 
            $order_xml[$cml['currency']] = '643';
        }
        if ($this->company_id == 1825) { 
            $order_xml[$cml['currency']] = 'руб';
        }
        if ($this->company_id == 1829) { 
            $order_xml[$cml['currency']] = 'руб';
        }
        if ($this->company_id == 2058) { 
            $order_xml[$cml['currency']] = 'руб';
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
                    $cml['rate_discounts'] => $this->getRoundedUpPrice($rate_discounts),
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
        list($guid_product) = $this->getProductIdByFile($_product -> {$cml['id']});

        $article = strval($_product -> {$cml['article']});
        $barcode = strval($_product -> {$cml['bar']});

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
            if (empty($product_data) && $this->is_allow_product_variations) {
                $product_data = $this->db->getRow(
                    'SELECT product_id, update_1c FROM ?:products WHERE external_id LIKE ?l AND parent_product_id = ?i',
                    $guid_product . '#%', 0
                );
            }
            if (empty($product_data)) {
                // if ($this->company_id != '1815') {
                //     $full_name = '';
                //     if (isset($_product -> {$cml['value_fields']} -> {$cml['value_field']})) {
                //         $requisites = $_product -> {$cml['value_fields']} -> {$cml['value_field']};
                //         list($full_name, $product_code, $html_description) = $this->getAdditionalDataProduct($requisites, $cml);
                //     }

                //     $cond = $this->db->quote('pd.product = ?s', trim(strval($_product -> {$cml['name']})));
                //     if (trim($full_name)) {
                //         $cond = $this->db->quote("( $cond OR pd.product = ?s )", $full_name);
                //     }

                //     $product_data = $this->db->getRow(
                //         "SELECT ?:products.product_id, update_1c FROM ?:products LEFT JOIN ?:product_descriptions as pd ON pd.product_id = ?:products.product_id AND pd.lang_code = ?s WHERE $cond $company_condition", DESCR_SL
                //     );
                // }

                if (empty($product_data) && !empty($article)) {
                    $product_data = $this->db->getRow(
                        "SELECT ?:products.product_id, update_1c FROM ?:products LEFT JOIN ?:product_descriptions as pd ON pd.product_id = ?:products.product_id AND pd.lang_code = ?s WHERE product_code = ?s $company_condition", DESCR_SL,
                        strval($_product -> {$cml['article']})
                    );
                }
            }
        }
        return $product_data;
    }

    public function addDataProductByFile($guid_product, $offers, $cml, $categories_commerceml, $import_params)
    {
        $xml_product_data = reset($offers);
        $allow_import_features = $this->s_commerceml['exim_1c_allow_import_features'];
        $add_tax = $this->s_commerceml['exim_1c_add_tax'];
        $link_type = $this->s_commerceml['exim_1c_import_type'];
        $log_message = "";

        if (empty($xml_product_data -> {$cml['name']})) {
            $log_message = "Name is not set for product with id: " . $xml_product_data -> {$cml['id']};

            return $log_message;
        }

        $product_data = $this->getProductDataByLinkType($link_type, $xml_product_data, $cml);

        $product_update = !empty($product_data['update_1c']) ? $product_data['update_1c'] : 'Y';
        $product_id = (!empty($product_data['product_id'])) ? $product_data['product_id'] : 0;

        $product_status = $xml_product_data->attributes()->{$cml['status']};
        if (!empty($this->features_commerceml['status']) && isset($xml_product_data->{$cml['properties_values']}->{$cml['property_values']})) {
            foreach ($xml_product_data->{$cml['properties_values']} -> {$cml['property_values']} as $_feature) {
                if ($_feature -> {$cml['id']} == $this->features_commerceml['status']['id']) {
                    $product_status = $this->features_commerceml['status']['variants'][strval($_feature -> {$cml['value']})]['value'];
                }
            }
        }

        if (!empty($product_status) && (string) $product_status == $cml['delete']) {
            if ($product_id != 0) {
                fn_delete_product($product_id);
                $log_message = "\n Deleted product: " . strval($xml_product_data -> {$cml['name']});
            }

            return $log_message;
        }

        if (!empty($xml_product_data -> {$cml['status']}) && strval($xml_product_data -> {$cml['status']}) == $cml['delete']) {
            if ($product_id != 0) {
                fn_delete_product($product_id);
                $log_message = "\n Deleted product: " . strval($xml_product_data -> {$cml['name']});
            }

            return $log_message;
        }

        if ($this->checkUploadProduct($product_id, $product_update)) {
            if (Registry::get('runtime.company_id') == '1815') $this->s_commerceml['exim_1c_import_product_name'] = 'full_name';
            $product = $this->dataProductFile($xml_product_data, $product_id, $guid_product, $categories_commerceml, $import_params);

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

            if ((isset($xml_product_data->{$cml['properties_values']}->{$cml['property_values']}) || isset($xml_product_data->{$cml['manufacturer']})) && ($allow_import_features == 'Y') && (!empty($this->features_commerceml))) {
                $product = $this->dataProductFeatures($xml_product_data, $product, $import_params);
                if (!empty($this->features_commerceml['sticker'])) {
                    foreach ($xml_product_data -> {$cml['properties_values']} -> {$cml['property_values']} as $_feature) {
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
                if (!empty($this->features_commerceml['product']) && $this->company_id == 1815) {
                    foreach ($xml_product_data -> {$cml['properties_values']} -> {$cml['property_values']} as $_feature) {
                        $feature_id = strval($_feature -> {$cml['id']});
                        if ($this->features_commerceml['product']['id'] != $feature_id) {
                            continue;
                        }
                        $p_feature_name = (string) $_feature->{$cml['value']};
                        if (!empty($p_feature_name)) {
                            $product['search_words'] = $product['product'];
                            $product['product'] = $p_feature_name;
                        }
                    }
                }
            }

            if (isset($xml_product_data->{$cml['value_fields']}->{$cml['value_field']})) {
                $this->dataProductFields($xml_product_data, $product);
            }

            if (isset($xml_product_data->{$cml['taxes_rates']}) && ($add_tax == 'Y')) {
                $product['tax_ids'] = $this->addProductTaxes($xml_product_data->{$cml['taxes_rates']}, $product_id);
            }

            if (!empty($xml_product_data->{$cml['base_unit']}->attributes())) {
                $product['measure'] = (string) $xml_product_data->{$cml['base_unit']}->attributes()->{$cml['full_name_unit']};
            }

            if ($this->company_id == '29') {
                $product['full_description'] = strval($xml_product_data -> {$cml['bar']});
            } elseif ($this->company_id == '1815') {
                foreach ($xml_product_data -> {$cml['properties_values']} -> {$cml['property_values']} as $_feature) {
                    $feature_id = strval($_feature -> {$cml['id']});
                    if ($this->features_commerceml['product_description']['id'] == $feature_id) {
                        $product['full_description'] = strval($_feature -> {$cml['value']});
                    }
                }
            }

            // limit for pinta for Katerina. TODO remove it from code as soon as finish with pinta.
            if (in_array($this->company_id, array(41, 46)) && $product_id) {
                $product = array('external_id' => $product['external_id']);
            }

            fn_set_hook('exim_1c_pre_update_product', $product, $product_id, $xml_product_data, $cml);

            $product_id = fn_update_product($product, $product_id, $import_params['lang_code']);

            $log_message = "\n Added product: " . $product['product'] . " commerceml_id: " . strval($xml_product_data->{$cml['id']});

            // import barcode to feature
            $data = json_decode(json_encode($xml_product_data), true);
            $data = array_filter($data, static function($val) {
                return (!is_array($val));
            });
            array_walk($data, 'fn_trim_helper');
            $company_condition = fn_get_company_condition('company_id', true, '', false, true);
            $base_features = db_get_hash_single_array("SELECT feature_code, feature_id FROM ?:product_features WHERE feature_code IN (?a) $company_condition", ['feature_id', 'feature_code'], array_keys($data));

            if (!empty($base_features)) {
                $variant_data['product_id'] = $product_id;
                foreach ($base_features as $variant_data['feature_id'] => $feature) {
                    $variant = $data[$feature];
                    list($d_variant, $params_variant) = $this->checkFeatureVariant($variant_data['feature_id'], $variant, $import_params['lang_code']);
                    if (!empty($d_variant)) {
                        $variant_data['variant_id'] = $d_variant;
                    } else {
                        $variant_data['variant_id'] = fn_add_feature_variant($variant_data['feature_id'], array('variant' => $variant));
                    }

                    $this->addFeatureValues($variant_data);
                }
            }
            // import barcode to feature

            // Import product features
            if (!empty($product['features'])) {
                $variants_data['product_id'] = $product_id;
                $variants_data['lang_code'] = $import_params['lang_code'];
                $variants_data['category_id'] = $product['category_id'];
                $this->addProductFeatures($product['features'], $variants_data, $import_params);

                if ($this->is_allow_product_variations) {
                    VariationsServiceProvider::getSyncService()->onTableChanged('product_features_values', $product_id);
                }
            }

            // Import images
            if (isset($xml_product_data->{$cml['image']})) {
                $this->addProductImage($xml_product_data->{$cml['image']}, $product_id, $import_params);
            }

            // Import combinations
            if (isset($xml_product_data->{$cml['product_features']}->{$cml['product_feature']})) {
                if ($this->is_allow_product_variations) {
                    $this->importProductOffersAsVariations($guid_product, $offers, [], $import_params);
                } else {
                    foreach ($offers as $combination_id => $offer) {
                        $combination_id = (strpos(strval($xml_product_data->{$cml['id']}), '#') == false) ? 0 : $combination_id;
                        $this->addProductXmlFeaturesAsOptions($offer, $product_id, $import_params, $combination_id);
                    }
                }
            }
        }

        return $log_message;
    }

    public function getProductStatusByAmount($amount)
    {
        $product_status = self::PRODUCT_STATUS_ACTIVE;

        if ($amount !== '' && $this->s_commerceml['exim_1c_add_out_of_stock'] == 'Y' && $amount <= 0) {
            $product_status = self::PRODUCT_STATUS_HIDDEN;
        }

        return $product_status;
    }

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

        $user_xml[$cml['address']][$cml['presentation']] = "$address1 $address2";
        // $user_xml[$cml['address']][][$cml['address_field']] = array(
        //     $cml['type'] => $cml['post_code'],
        //     $cml['value'] => $zipcode
        // );
        // $user_xml[$cml['address']][][$cml['address_field']] = array(
        //     $cml['type'] => $cml['country'],
        //     $cml['value'] => $country
        // );
        // $user_xml[$cml['address']][][$cml['address_field']] = array(
        //     $cml['type'] => $cml['city'],
        //     $cml['value'] => $city
        // );
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

    public function importFileOrders($xml)
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
            fn_define('ORDER_MANAGEMENT', true);
            foreach ($orders_data as $order_data) {
                $order_info = $xml_products = [];
                $external_order_id = strval($order_data->{$cml['id']});
                $external_order_number = strval($order_data->{$cml['number']});

                //Check the database for an order with the specified ID exported from the accounting system
                if ($external_order_id && $external_order_id === (string) (int) $external_order_id) {
                    $order_info = fn_get_order_info($external_order_id);
                }

                //If order was not found by external_id try to find it by external order number
                if (empty($order_info['order_id'])) {
                    $order_info = fn_get_order_info($external_order_number);
                }

                // do not disturb old orders
                // if (empty($order_info['order_id']) || $order_info['timestamp'] + SECONDS_IN_DAY * 14 < time()) {
                //     continue;
                // }
                $order_id = $order_info['order_id'];
                foreach ($order_data->{$cml['products']}->{$cml['product']} as $xml_product) {
                    $product_data = $this->getProductDataByLinkType($link_type, $xml_product, $cml);
                    $xml_products[$product_data['product_id']] = $xml_products[$product_data['product_id']] ?? 0;
                    $xml_products[$product_data['product_id']] += intval($xml_product->{$cml['amount']});
                }
                $order_products = array_filter(fn_array_column($order_info['products'], 'amount', 'product_id'));

                if (!empty(array_diff_assoc($xml_products, $order_products) + array_diff_assoc($order_products, $xml_products))) {
                    if (!empty($order_info['user_id'])) {
                        $_data = db_get_row("SELECT user_id, user_login as login FROM ?:users WHERE user_id = ?i", $order_info['user_id']);
                    }
                    $customer_auth = fn_fill_auth($_data, array(), false, 'C');

                    fn_form_cart($order_id, $cart, $customer_auth);
                    fn_store_shipping_rates($order_id, $cart, $customer_auth);
                    $cart['order_id'] = $order_id;
                    //$cart['order_status'] = $statuses[strval($data_field->{$cml['value']})]['status'];
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
                        fn_add_product_to_cart($_item, $cart, $customer_auth);
                    }

                    foreach ($order_data->{$cml['value_fields']}->{$cml['value_field']} as $data_field) {
                        // TODO move to settings
                        if ($data_field->{$cml['name']} == 'Дата отгрузки по 1С' && !empty(strtotime(strval($data_field->{$cml['value']})))) {
                            $cart['delivery_date'] = strtotime(strval($data_field->{$cml['value']}));
                        }
                    }
                    $backup_auth = Tygh::$app['session']['auth'];
                    Tygh::$app['session']['auth'] = $customer_auth;

                    fn_calculate_cart_content($cart, $customer_auth);
                    if (!fn_cart_is_empty($cart)) {
                        fn_place_order($cart, $customer_auth, 'save');
                    }
                    Tygh::$app['session']['auth'] = $backup_auth;
                }

                foreach ($order_data->{$cml['value_fields']}->{$cml['value_field']} as $data_field) {
                    if ($data_field->{$cml['name']} == $cml['status_order'] && !empty($statuses[strval($data_field->{$cml['value']})])) {
                        $status_to = strval($data_field->{$cml['value']});
                    }
                }

                if (!empty($status_to) && $order_info['status'] != $statuses[$status_to]['status']) {
                    fn_change_order_status($order_info['order_id'], $statuses[$status_to]['status']);
                }

                fn_set_hook('exim_1c_update_order', $order_data, $cml);

                unset($status_to);
                unset($order_info);
            }
        }
    }

    public function getPricesDataFromFile($prices_file, $data_prices)
    {
        $cml = $this->cml;
        $prices_commerseml = array();
        foreach ($prices_file -> {$cml['price_type']} as $_price) {
            $found = false;
            foreach ($data_prices as $d_price) {
                if ($d_price['price_1c'] == trim(strval($_price -> {$cml['name']}))) {
                    $d_price['external_id'] = strval($_price -> {$cml['id']});
                    $prices_commerseml[] = $d_price;
                    $found = true;
                }
            }
            if (!$found) {
                $like_name = '%' . strval($_price -> {$cml['name']}) . '%';
                $user_id = 0;
                list($users, ) = fn_get_users(array('search_query' => strval($_price -> {$cml['name']}), 'user_type' => 'C', 'extended_search' => false), $_SESSION['auth'], 10);
                if (!empty($users)) {
                    $user_id = reset($users)['user_id'];
                }

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
        if (empty($fake_product_data['prices'])) {
            unset($fake_product_data['prices']);
        }

        $is_product_shared_to_company = false;
        $is_product_shared = false;
        if (fn_allowed_for('ULTIMATE')) {
            $is_product_shared_to_company = fn_ult_is_shared_product($product_id, $this->company_id) === 'Y';
            $is_product_shared = fn_ult_is_shared_product($product_id) === 'Y';
        }
        $is_product_owned_by_company = $this->getProductCompany($product_id) == $this->company_id;

        if ($this->has_stores && (
            $is_product_shared_to_company ||
            $is_product_shared && $is_product_owned_by_company
        )) {
            fn_update_product_prices($product_id, $fake_product_data, $this->company_id);
        }

        if ($is_product_owned_by_company) {
            fn_update_product_prices($product_id, $fake_product_data);

            // List price updating
            if (isset($prices['list_price'])) {
                $this->db->query(
                    'UPDATE ?:products SET list_price = ?d WHERE product_id = ?i',
                    $prices['list_price'],
                    $product_id
                );
            }
        }
    }
    // send price only to limited products
    public function dataOrderProducts($xml, $order_data, $discount = 0)
    {
        $cml = $this->cml;

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
                        $cml['total'] => $this->getRoundedUpPrice($product_discount),
                        $cml['in_total'] => 'false'
                    );
                }
            }

            if(!empty($product['discount'])) {
                $product['base_price'] = $product['base_price'] ?? $product['price'];
                $data_product[$cml['discounts']][][$cml['discount']] = array(
                    $cml['name'] => $cml['product_discount'],
                    $cml['total'] => $product['discount'],
                    $cml['rate_discounts'] => round((1 - $product['price'] / $product['base_price']) * 100),
                    $cml['in_total'] => 'true'
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

                $product_subtotal = $product['subtotal'] + $this->getRoundedUpPrice($tax_value);
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
            // todo remove after konix change on their side
            if ($send_price_1c[$product['product_id']] != 'Y') {
                unset($data_product[$cml['price_per_item']], $data_product[$cml['total']]);
            }
            if (isset($product['extra']['usergroup_id']) && $product['extra']['usergroup_id'] && array_key_exists($product['extra']['usergroup_id'], $this->data_prices)) {
                $data_product[$cml['price_type']] = $this->data_prices[$product['extra']['usergroup_id']]['price_1c'];
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
                $feature_name = trim(strval($_feature -> {$cml['name']}));

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
                } elseif ($feature_name == 'О продукте') {
                    $features_import['product_description']['id'] = strval($_feature -> {$cml['id']});
                    $features_import['product_description']['name'] = 'product_description';
                } elseif ($feature_name == 'Статус') {
                    $features_import['status']['id'] = strval($_feature -> {$cml['id']});
                    $features_import['status']['name'] = 'status';
                    if (!empty($_feature -> {$cml['variants_values']})) {
                        $_feature_data = $_feature -> {$cml['variants_values']} -> {$cml['directory']};
                        foreach ($_feature_data as $_variant) {
                            $_variants[strval($_variant -> {$cml['id_value']})]['id'] = strval($_variant -> {$cml['id_value']});
                            $_variants[strval($_variant -> {$cml['id_value']})]['value'] = strval($_variant -> {$cml['value']});
                            $f_variants[strval($_variant -> {$cml['id_value']})]['external_id'] = strval($_variant -> {$cml['id_value']});
                            $f_variants[strval($_variant -> {$cml['id_value']})]['variant'] = strval($_variant -> {$cml['value']});
                        }
                    }
                    $features_import['status']['variants'] = $_variants;
                } elseif ($feature_name == '*Название для сайта#') {
                    $features_import['product']['id'] = strval($_feature -> {$cml['id']});
                }

                fn_set_hook('exim_1c_import_features_definition', $features_import, $feature_name, $_feature, $cml);

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

                if (empty($feature_id)) {
                    $feature_id = $this->db->getField("SELECT ?:product_features.feature_id FROM ?:product_features LEFT JOIN ?:product_features_descriptions ON ?:product_features.feature_id = ?:product_features_descriptions.feature_id AND lang_code = ?s WHERE ?:product_features_descriptions.description = ?s AND ?:product_features.company_id = ?i", $import_params['lang_code'], strval($_feature -> {$cml['name']}), $company_id);
                    if ($feature_id) {
                        $this->db->query("UPDATE ?:product_features SET external_id = ?s WHERE feature_id = ?i", strval($_feature -> {$cml['id']}), $feature_id);
                    }
                }

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

                        if ($new_feature && fn_allowed_for('ULTIMATE')) {
                            fn_ult_update_share_object($feature_id, 'product_features', $company_id);
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

            if ($feature_id == 0 && fn_allowed_for('ULTIMATE')) {
                fn_ult_update_share_object($_feature_id, 'product_features', $company_id);
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

            if (
                empty($status)
                || empty($user_data)
                || empty($user_data['password'])
                || !fn_user_password_verify((int) $user_data['user_id'], (string) $_SERVER['PHP_AUTH_PW'], $user_data['password'], $salt)
            ) {
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

                fn_set_hook('exim_1c_import_value_fields', $product, $value_field, $_name_field, $_v_field, $cml);

                // TODO move string to add-on settings
                if (in_array($_name_field, array('КвантЗаказа'))) {
                    $product['qty_step'] = (float) $_v_field;
                }
                if ($_name_field == $cml['avail_till']) $product['avail_till'] = fn_parse_date($_v_field, true);

                if ($_name_field == $cml['tracking']) {
                    if ($_v_field == $cml['yes']) {
                        $product['tracking'] = ProductTracking::TRACK_WITH_OPTIONS;
                    } else {
                        $product['tracking'] = ProductTracking::DO_NOT_TRACK;
                    }
                }
            }
        }
    }

    public function exportDataOrders($lang_code)
    {
        $params = array(
            'company_id' => $this->company_id,
            'company_name' => true,
            'place' => 'exim_1c',
        );

        $statuses = $this->s_commerceml['exim_1c_order_statuses'];
        if (!empty($statuses)) {
            foreach($statuses as $key => $status) {
                if (!empty($status)) {
                    $params['status'][] = $key;
                }
            }
        }
        if ($this->company_id) {
            $statuses = db_get_field('SELECT export_statuses FROM ?:companies WHERE company_id = ?i', $this->company_id);
            if (!empty($statuses)) {
                $params['status'] = explode(',', $statuses);
            }
        }

        list($orders, $search) = fn_get_orders($params);
        header("Content-type: text/xml; charset=utf-8");
        fn_echo("\xEF\xBB\xBF");
        $xml = new \XMLWriter();
        $xml -> openMemory();
        $xml -> startDocument();
        $xml -> startElement($this->cml['commerce_information']);
        foreach ($orders as $k => $data) {
            $order_data = fn_get_order_info($data['order_id']);
            $xml = $this->dataOrderToFile($xml, $order_data, $lang_code);
        }
        $xml -> endElement();
        fn_echo($xml -> outputMemory());
    }

    public function exportDataOrdersGetXML($lang_code) {
        $params = array(
            'company_id' => $this->company_id,
            'company_name' => true,
            'place' => 'exim_1c',
        );

        $statuses = $this->s_commerceml['exim_1c_order_statuses'];
        if (!empty($statuses)) {
            foreach($statuses as $key => $status) {
                if (!empty($status)) {
                    $params['status'][] = $key;
                }
            }
        }
        if ($this->company_id) {
            $statuses = db_get_field('SELECT export_statuses FROM ?:companies WHERE company_id = ?i', $this->company_id);
            if (!empty($statuses)) {
                $params['status'] = explode(',', $statuses);
            }
        }

        list($orders, $search) = fn_get_orders($params);
        
        $begin = "\xEF\xBB\xBF";
        $xml = new \XMLWriter();
        $xml -> openMemory();
        $xml -> startDocument();
        $xml -> startElement($this->cml['commerce_information']);
        foreach ($orders as $k => $data) {
            $order_data = fn_get_order_info($data['order_id']);
            $xml = $this->dataOrderToFile($xml, $order_data, $lang_code);
        }
        $xml -> endElement();
        $data = $begin . $xml -> outputMemory();
        return $data;
    }

    public function updateProductStatus($product_id, $product_data, $amount)
    {
        if (!is_numeric($amount)) {
            return self::PRODUCT_STATUS_ACTIVE;
        }

        $product_status = $this->getProductStatusByAmount($amount);

        if ($product_data['tracking'] != ProductTracking::DO_NOT_TRACK) {
            $this->db->query(
                'UPDATE ?:products SET status = ?s WHERE update_1c = ?s AND product_id = ?i',
                $product_status,
                'Y',
                $product_id
            );
        }

        return $product_status;
    }

    public function dataProductFeatures($data_product, $product, $import_params)
    {
        $property_for_promo_text = trim($this->s_commerceml['exim_1c_property_product']);
        $cml = $this->cml;
        $features_commerceml = $this->features_commerceml;

        if (!empty($data_product -> {$cml['properties_values']} -> {$cml['property_values']})) {
            foreach ($data_product -> {$cml['properties_values']} -> {$cml['property_values']} as $_feature) {
                $variant_data = array();
                $feature_id = strval($_feature -> {$cml['id']});

                fn_set_hook('exim_1c_import_features_values', $product, $_feature, $features_commerceml, $cml);

                if (!isset($features_commerceml[$feature_id])) {
                    continue;
                }

                if (!isset($_feature -> {$cml['value']}) || trim(strval($_feature -> {$cml['value']})) == '') {
                    continue;
                }

                $p_feature_name = (string) $_feature->{$cml['value']};
                if (!empty($features_commerceml[$feature_id]['variants'])) {
                    $p_feature_name = empty($features_commerceml[$feature_id]['variants'][$p_feature_name]['value'])
                        ? $p_feature_name
                        : (string) $features_commerceml[$feature_id]['variants'][$p_feature_name]['value'];
                }

                $feature_name = trim($features_commerceml[$feature_id]['name'], " ");
                if (!empty($features_commerceml[$feature_id])) {
                    $product_params = $this->dataShippingParams($p_feature_name, $feature_name);

                    if (!empty($product_params)) {
                        $product = array_merge($product, $product_params);
                    }

                    if (!empty($property_for_promo_text) && ($property_for_promo_text == $feature_name)) {
                        if (!empty($features_commerceml[$feature_id]['variants'])) {
                            $product['promo_text'] = $features_commerceml[$feature_id]['variants'][$p_feature_name]['value'];
                        } else {
                            $product['promo_text'] = $p_feature_name;
                        }
                    }
                }

                if (!empty($features_commerceml[$feature_id]['id'])) {
                    $variant_data['feature_id'] = $features_commerceml[$feature_id]['id'];
                    $variant_data['feature_types'] = $features_commerceml[$feature_id]['type'];
                    $variant_data['feature_type'] = $features_commerceml[$feature_id]['type'];
                    $variant_data['lang_code'] = $import_params['lang_code'];
                    $variant_data['feature_type'] = $features_commerceml[$feature_id]['type'];

                    $d_variants = fn_get_product_feature_data($variant_data['feature_id'], true, false, $import_params['lang_code']);

                    if (!empty($d_variants['feature_id']) && $d_variants['feature_id'] == $variant_data['feature_id']) {
                        $variant_data = $d_variants;
                    }

                    if ($variant_data['feature_type'] == ProductFeatures::NUMBER_SELECTBOX) {
                        $p_feature_name = str_replace(',', '.', $p_feature_name);
                        $variant_data['value_int'] = $p_feature_name;
                    }

                    if ($variant_data['feature_type'] == ProductFeatures::NUMBER_FIELD) {
                        $p_feature_name = str_replace(',', '.', $p_feature_name);
                        $variant_data['value_int'] = $p_feature_name;
                    }

                    $is_id = false;
                    $variant = '';
                    if (!empty($features_commerceml[$feature_id]['variants'])) {
                        foreach ($features_commerceml[$feature_id]['variants'] as $_variant) {
                            if ($p_feature_name == $_variant['id']) {
                                $variant = $_variant['value'];
                                $is_id = true;
                                break;
                            }
                        }

                        if (!$is_id) {
                            $variant = $p_feature_name;
                        }
                    } else {
                        $variant = $p_feature_name;
                    }
                    $variant_data['variant'] = $variant;

                    if ($variant_data['feature_type'] == ProductFeatures::TEXT_FIELD) {
                        $variant_data['value'] = $variant;
                    }

                    list($d_variant, $params_variant) = $this->checkFeatureVariant($variant_data['feature_id'], $variant_data['variant'], $import_params['lang_code']);
                    if (!empty($d_variant)) {
                        $variant_data['variant_id'] = $d_variant;
                    } else {
                        $variant_data['variant_id'] = fn_add_feature_variant($variant_data['feature_id'], array('variant' => $variant));
                    }

                    $product['features'][$feature_id] = $variant_data;
                }
            }
        }

        $variant_data = array();
        if ($this->s_commerceml['exim_1c_used_brand'] == 'field_brand') {
            if (isset($data_product -> {$cml['manufacturer']})) {
                $variant_data['feature_id'] = $features_commerceml['brand1c']['id'];
                $variant_data['lang_code'] = $import_params['lang_code'];
                $variant_id = $this->db->getField(
                    "SELECT variant_id"
                    . " FROM ?:product_feature_variants"
                    . " WHERE feature_id = ?i AND external_id = ?s",
                    $variant_data['feature_id'],
                    strval($data_product -> {$cml['manufacturer']} -> {$cml['id']})
                );

                $variant = strval($data_product -> {$cml['manufacturer']} -> {$cml['name']});
                if (empty($variant_id)) {
                    $variant_data['variant_id'] = fn_add_feature_variant($variant_data['feature_id'], array('variant' => $variant));
                    $this->db->query("UPDATE ?:product_feature_variants SET external_id = ?s WHERE variant_id = ?i", strval($data_product -> {$cml['manufacturer']} -> {$cml['id']}), $variant_data['variant_id']);
                } else {
                    $variant_data['variant_id'] = $variant_id;
                }

                $product['features'][$variant_data['feature_id']] = $variant_data;
            }
        }

        return $product;
    }

    public function getOrderDataForXml($order_data, $cml)
    {
        $store_currencies = self::dataProductCurrencies();

        if (!empty($store_currencies)) {
            $order_currency = (!empty($order_data['secondary_currency'])) ? $order_data['secondary_currency'] : CART_PRIMARY_CURRENCY;
            $currency = '';
            foreach ($store_currencies as $currency_name => $currency_value) {
                if ($order_currency == $currency_value['currency_code']) {
                    $currency = $currency_name;
                    break;
                }
            }
        } else {
            $currency = (!empty($order_data['secondary_currency'])) ? $order_data['secondary_currency'] : CART_PRIMARY_CURRENCY;
        }

        $notes = $order_data['notes'];
        if ($this->company_id == 1815) {
            $n[] = $order_data['payment_info']['customer_phone'] ?? $order_data['phone'];
            if (!empty($order_data['s_address'])) {
                $n[] = $order_data['s_address'];
            } else {
                $user = fn_get_user_info($order_data['user_id']);
                $n[] = $user['s_address'];
            }
            $n[] = $order_data['shipping'][0]['shipping'];
            if (!empty($notes)) $n[] = $notes;
            $notes = implode('; ', $n);
        }

        $array_order_xml = array(
            $cml['id'] => $order_data['order_id'],
            $cml['number'] => $order_data['order_id'],
            $cml['date'] => date('Y-m-d', $order_data['timestamp']),
            $cml['time'] => date('H:i:s', $order_data['timestamp']),
            $cml['operation'] => $cml['order'],
            $cml['role'] => $cml['seller'],
            $cml['rate'] => 1,
            $cml['total'] => $order_data['total'],
            $cml['currency'] => $currency,
            $cml['notes'] => $notes
        );

        return $array_order_xml;
    }

    public function getCompanyIdByLinkType($link_type, $_group)
    {
        if ($link_type == 'name') {
            $category_ids = $this->db->getColumn('SELECT category_id FROM ?:category_descriptions WHERE category = ?s', strval($_group -> {$this->cml['name']}));

        } else {
            $category_ids = $this->db->getColumn('SELECT category_id FROM ?:categories WHERE external_id LIKE ?l', '%'.strval($_group -> {$this->cml['id']}).'%');
        }

        return $category_ids;
    }
}
