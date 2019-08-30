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

use Tygh\Registry;
use Tygh\Addons\ProductVariations\Product\Manager as ProductManager;
use Tygh\Addons\ProductVariations\Product\AdditionalDataLoader;
use Tygh\Common\OperationResult;
use Tygh\Enum\ProductTracking;
use Tygh\Storage;
use Tygh\BlockManager\Block;
use Tygh\BlockManager\ProductTabs;
use Tygh\Languages\Languages;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * Add-on install handler
 */
function fn_product_variations_install()
{
    if (fn_allowed_for('ULTIMATE')) {
        $company_ids = fn_get_all_companies_ids();
    } else {
        $company_ids = array(0);
    }

    $block = Block::instance();
    $product_tabs = ProductTabs::instance();

    foreach ($company_ids as $company_id) {
        $block_data = array(
            'type'         => 'products',
            'properties'   => array(
                'template'                                       => 'addons/product_variations/blocks/products/variations_list.tpl',
                'product_variations.hide_add_to_wishlist_button' => 'N',
                'hide_add_to_cart_button'                        => 'N',
                'product_variations.show_product_code' => 'Y',
                'product_variations.show_variation_thumbnails' => 'Y',
            ),
            'content_data' => array(
                'content' => array(
                    'items' => array(
                        'filling'             => 'product_variations.variations_filling',
                        'limit'               => '10',
                        'variations_in_stock' => 'N',
                    ),
                ),
            ),
            'company_id'   => $company_id,
        );

        $block_description = array(
            'lang_code' => DEFAULT_LANGUAGE,
            'name' => __('product_variations.variations_list_block_name', array(), DEFAULT_LANGUAGE),
        );

        $block_id = $block->update($block_data, $block_description);

        $tab_data = array(
            'tab_type'      => 'B',
            'block_id'      => $block_id,
            'template'      => '',
            'addon'         => 'product_variations',
            'status'        => 'A',
            'is_primary'    => 'N',
            'position'      => false,
            'product_ids'   => null,
            'company_id'    => $company_id,
            'show_in_popup' => 'Y',
            'lang_code'     => DEFAULT_LANGUAGE,
            'name'          => __('product_variations.variations_list_tab_name', array(), DEFAULT_LANGUAGE),
        );

        $product_tabs->update($tab_data);
    }

    $exim_layout = array(
        'name' => 'variations',
        'cols' => 'Product code,Product type,Language,Product id,Product name,Variation options,Options',
        'pattern_id' => 'products',
        'active' => 'Y',
    );

    db_query('INSERT INTO ?:exim_layouts ?e', $exim_layout);
}

/**
 * Returns a value indicating whether the give value is "empty".
 *
 * The value is considered "empty", if one of the following conditions is satisfied:
 *
 * - it is `null`,
 * - an empty string (`''`),
 * - a string containing only whitespace characters,
 * - or an empty array.
 *
 * @param mixed $value
 *
 * @return bool if the value is empty
 */
function fn_product_variations_value_is_empty($value)
{
    return $value === '' || $value === array() || $value === null || is_string($value) && trim($value) === '';
}

/**
 * Combines the features of a parent product and the features of the selected product variation.
 *
 * @param array     $product    Product data
 * @param string    $display_on Filter by display on field
 *
 * @return array
 */
function fn_product_variations_merge_features($product, $display_on = 'C')
{
    if (empty($product['variation_product_id'])) {
        return $product;
    }

    $product_variation = array(
        'product_id' => $product['variation_product_id'],
        'category_ids' => $product['category_ids']
    );

    // if $product from fn_get_product_data
    if (isset($product['detailed_params']['info_type']) && $product['detailed_params']['info_type'] === 'D' && $display_on !== 'A') {
        $params = array(
            'category_ids' => fn_get_category_ids_with_parent($product['category_ids']),
            'product_id' => $product['variation_product_id'],
            'product_company_id' => !empty($product['company_id']) ? $product['company_id'] : 0,
            'statuses' => AREA == 'C' ? array('A') : array('A', 'H'),
            'variants' => true,
            'plain' => false,
            'display_on' => AREA == 'A' ? '' : 'product',
            'existent_only' => (AREA != 'A'),
            'variants_selected_only' => true
        );

        list($product_features) = fn_get_product_features($params, 0, CART_LANGUAGE);

        if (!empty($product['product_features'])) {
            foreach ($product['product_features'] as $feature_id => &$item) {
                if (!isset($product_features[$feature_id])) {
                    continue;
                }

                if (isset($item['subfeatures'])) {
                    $item['subfeatures'] = array_replace($item['subfeatures'], $product_features[$feature_id]['subfeatures']);
                } else {
                    $item = array_replace($item, $product_features[$feature_id]);
                }
            }
            unset($item);
        } else {
            $product['product_features'] = $product_features;
        }

        if (isset($product['header_features'])) {
            $header_features = fn_get_product_features_list($product_variation, 'H');

            $product['header_features'] = fn_array_elements_to_keys($product['header_features'], 'feature_id');
            $header_features = fn_array_elements_to_keys($header_features, 'feature_id');
            $product['header_features'] = array_replace($product['header_features'], $header_features);

            $product['header_features'] = array_values($product['header_features']);
        }
    } else {
        $product_features = fn_get_product_features_list($product_variation, $display_on);
        $product_features = fn_array_elements_to_keys($product_features, 'feature_id');
        $product['product_features'] = fn_array_elements_to_keys($product['product_features'], 'feature_id');

        $product['product_features'] = array_replace($product['product_features'], $product_features);
        $product['product_features'] = array_values($product['product_features']);
    }

    return $product;
}


/**
 * Gets count of the product files.
 *
 * @param int       $product_id         Product identifier.
 * @param string    $status             Files status (A|D)
 * @param array     $excluded_file_ids  List of the file identifiers that will be excluded from the calculation
 *
 * @return int
 */
function fn_product_variations_get_product_files_count($product_id, $status, array $excluded_file_ids = array())
{
    $condition = db_quote('product_id = ?i AND status = ?s', $product_id, $status);

    if (!empty($excluded_file_ids)) {
        $condition .= db_quote(' AND file_id NOT IN (?n)', $excluded_file_ids);
    }

    return (int) db_get_field('SELECT COUNT(*) AS cnt FROM ?:product_files WHERE ?p', $condition);
}

/**
 * Fetches available options based on existent variations
 *
 * @param int    $product_id Product id
 * @param string $lang_code  Language code
 *
 * @return \Tygh\Common\OperationResult
 */
function fn_product_variations_get_available_options($product_id, $lang_code = CART_LANGUAGE)
{
    /** @var \Tygh\Common\OperationResult $result */
    $result = new OperationResult();

    /** @var ProductManager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    $product_options = fn_get_product_options($product_id, $lang_code, true, true, true);

    if ($product_manager->hasProductVariations($product_id)) {
        $data = array();
        $used_option_ids = $product_manager->getProductVariationOptionsValue($product_id);

        if (!empty($used_option_ids)) {

            foreach ($used_option_ids as $option_id) {
                // if there is an option, that used for existent variations, but now, the option is not available
                // we cannot generate variations
                if (!isset($product_options[$option_id])) {
                    $result->addError(
                        'unable_to_generate_variations',
                        __('product_variations.cannot_generate_variations_reason_options')
                    );
                    break;
                }

                $data[$option_id] = $product_options[$option_id];
            }
        } else {
            $data = $product_options;
        }

        $result->setData($data);
    } else {
        $result->setData($product_options);
    }

    $result->setSuccess(!$result->hasErrors());

    return $result;
}

/**
 * Fetches options combinations by provided options and variants and rearranges them
 *
 * @param array $product_data    Product data
 * @param array $product_options Array of product options
 *
 * @return array
 */
function fn_product_variations_get_options_combinations($product_data, $product_options)
{
    /** @var ProductManager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    $result = array();
    $selected_options_ids = $selected_variant_ids = array();

    foreach ($product_options as $option_id => $option) {
        $selected_options_ids[$option_id] = $option_id;

        if (isset($option['variants'])) {
            foreach ($option['variants'] as $variant_id => $variant) {
                if ($variant['status'] != 'A') {
                    continue;
                }

                $selected_variant_ids[$option_id][$variant_id] = $variant_id;
            }
        }
    }

    if (!empty($selected_options_ids) && !empty($selected_variant_ids)) {
        $options_combinations = fn_get_options_combinations($selected_options_ids, $selected_variant_ids);

        foreach ($options_combinations as $item) {
            // restore original options order
            $selected_options = array_reverse($item, true);
            $name_parts = array();
            $variation_code = $product_manager->getVariationCode($product_data['product_id'], $selected_options);

            $combination = array(
                'selected_options' => $selected_options,
                'variants' => array(),
                'exists' => $product_manager->existsProductVariation($product_data['product_id'], $variation_code)
            );

            foreach ($selected_options as $option_id => $variant_id) {
                $option = $product_options[$option_id];
                $variant = $option['variants'][$variant_id];
                $variant['option_name'] = $option['option_name'];

                $combination['variants'][$variant_id] = $variant;

                $name_parts[] = sprintf('%s: %s', $option['option_name'], $variant['variant_name']);
            }

            $combination['name'] = implode(', ', $name_parts);

            $result[$variation_code] = $combination;
        }
    }

    return $result;
}

/**
 * Gets variation by selected options.
 *
 * @param array $product          Product data
 * @param array $product_options  Product options
 * @param array $selected_options List of selected options
 * @param int   $index            Current variation index
 *
 * @return array
 */
function fn_product_variations_get_variation_by_selected_options(
    $product,
    $product_options,
    $selected_options,
    $index = 0
) {
    /** @var ProductManager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    $options = array();
    $name_parts = array('product_name' => $product['product']);
    $variation_code = $product_manager->getVariationCode($product['product_id'], $selected_options);

    foreach ($selected_options as $option_id => $variant_id) {
        $option_id = (int) $option_id;
        $variant_id = (int) $variant_id;

        $option = $product_options[$option_id];
        $option['value'] = $variant_id;

        $variant = $product_options[$option_id]['variants'][$variant_id];
        $name_parts[$option_id] = $option['option_name'] . ': ' . $variant['variant_name'];

        $options[$option_id] = $option;
    }

    $combination = array(
        'name'             => implode(', ', $name_parts),
        'price'            => $product['price'],
        'list_price'       => $product['list_price'],
        'weight'           => $product['weight'],
        'amount'           => empty($product['amount']) ? 1 : $product['amount'],
        'code'             => !empty($product['product_code']) ? $product['product_code'] . $index : '',
        'options'          => $options,
        'selected_options' => $selected_options,
        'variation'        => $variation_code,
        'status'           => 'A',
    );

    return $combination;
}

/**
 * Gets last used index for variation product code
 *
 * @param int $product_id Product id
 *
 * @return int
 */
function fn_product_variations_get_last_product_code_index($product_id)
{
    /** @var ProductManager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    $index_list = array(0);
    $product_code = $product_manager->getProductFieldValue($product_id, 'product_code');
    $variation_skus = db_get_fields('SELECT product_code FROM ?:products WHERE parent_product_id = ?i', $product_id);

    foreach ($variation_skus as $sku) {
        if (!empty($product_code) && strpos($sku, $product_code) === 0) {
            $index_list[] = (int) str_replace($product_code, '', $sku);
        }
    }

    return max($index_list);
}

/**
 * Hook handler: after delete all product option.
 */
function fn_product_variations_poptions_delete_product_pre($product_id)
{
    $child_products = db_get_fields(
        'SELECT product_id FROM ?:products WHERE parent_product_id = ?i AND product_type = ?s',
        $product_id, ProductManager::PRODUCT_TYPE_VARIATION
    );

    foreach ($child_products as $child_product_id) {
        fn_delete_product($child_product_id);
    }
}

/**
 * Hook handler: before selecting products.
 */
function fn_product_variations_get_products_pre(&$params, $items_per_page, $lang_code)
{
    /** @var ProductManager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    if (empty($params['pid'])) {
        if (!isset($params['product_type']) && empty($params['view_id'])) {
            $params['product_type'] = $product_manager->getCreatableProductTypes();
        }

        if (!isset($params['parent_product_id'])) {
            $params['parent_product_id'] = 0;
        }
     }

    if (empty($params['product_type'])) {
        $params['product_type'] = null;
    }

    if (!isset($params['parent_product_id'])) {
        $params['parent_product_id'] = null;
    }

    if (!isset($params['variation_code'])) {
        $params['variation_code'] = null;
    }

    if (!empty($params['variations_in_stock'])
        && $params['variations_in_stock'] == 'Y'
        && Registry::get('settings.General.inventory_tracking') == 'Y'
        && Registry::get('settings.General.allow_negative_amount') != 'Y'
    ) {
        $params['amount_from'] = ProductManager::PRODUCT_IN_STOCK_AMOUNT;
    }

    if (!is_array($params['parent_product_id'])) {
        $params['parent_product_id'] = explode(',', $params['parent_product_id']);
    }

    $params['product_type'] = (array) $params['product_type'];
    $params['parent_product_id'] = array_filter($params['parent_product_id']);
}

/**
 * Hook handler: before selecting products.
 */
function fn_product_variations_get_products($params, $fields, $sortings, &$condition, &$join, $sorting, $group_by, $lang_code, $having)
{
    if (!fn_product_variations_value_is_empty($params['product_type'])) {
        if (is_array($params['product_type'])) {
            $condition .= db_quote(' AND products.product_type IN (?a)', $params['product_type']);
        } else {
            $condition .= db_quote(' AND products.product_type = ?s', $params['product_type']);
        }
    }

    if (!fn_product_variations_value_is_empty($params['parent_product_id'])) {
        if (is_array($params['parent_product_id'])) {
            $condition .= db_quote(' AND products.parent_product_id IN (?n)', $params['parent_product_id']);
        } else {
            $condition .= db_quote(' AND products.parent_product_id = ?i', $params['parent_product_id']);
        }
    }

    if (!fn_product_variations_value_is_empty($params['variation_code'])) {
        if (is_array($params['variation_code'])) {
            $condition .= db_quote(' AND products.variation_code IN (?a)', $params['variation_code']);
        } else {
            $condition .= db_quote(' AND products.variation_code = ?s', $params['variation_code']);
        }
    }

    // FIXME Dirty hack
    if ((in_array(ProductManager::PRODUCT_TYPE_VARIATION, $params['product_type'], true) || !empty($params['pid']))
        && strpos($join, 'ON products_categories.product_id = products.product_id') !== false
    ) {
        $join = str_replace(
            'ON products_categories.product_id = products.product_id',
            'ON products_categories.product_id = products.parent_product_id OR products_categories.product_id = products.product_id',
            $join
        );
    }
}

/**
 * Hook handler: change SQL parameters for product data select
 */
function fn_product_variations_get_product_data($product_id, $field_list, &$join, $auth, $lang_code, $condition)
{
    // FIXME Dirty hack
    $join = str_replace(
        'ON ?:products_categories.product_id = ?:products.product_id',
        'ON ?:products_categories.product_id = ?:products.parent_product_id OR ?:products_categories.product_id = ?:products.product_id',
        $join
    );
}

/**
 * Hook handler: particularize product data
 */
function fn_product_variations_get_product_data_post(&$product_data, $auth, $preview, $lang_code)
{
    if (empty($product_data['product_type'])) {
        return;
    }

    if ($product_data['product_type'] === ProductManager::PRODUCT_TYPE_CONFIGURABLE) {
        $product_data['amount'] = db_get_field(
            'SELECT MAX(amount) FROM ?:products WHERE parent_product_id = ?i AND product_type = ?s',
            $product_data['product_id'], ProductManager::PRODUCT_TYPE_VARIATION
        );

        $product_data['detailed_params']['is_preview'] = $preview;

    } elseif ($product_data['product_type'] === ProductManager::PRODUCT_TYPE_VARIATION) {
        if (fn_allowed_for('ULTIMATE')) {
            $product_data['shared_product'] = fn_ult_is_shared_product($product_data['parent_product_id']);
            $product_data['shared_between_companies'] = fn_ult_get_shared_product_companies($product_data['parent_product_id']);
        }

        // Skip creating seo name
        $product_data['seo_name'] = $product_data['product_id'];
    }

    if (AREA === 'C') {
        $product_id = $product_data['product_id'];
        $selected_options = Registry::get('runtime.selected_options.' . $product_id);

        if ($selected_options) {
            $product_data['selected_options'] = $selected_options;
        }
    }
}

/**
 * Hook handler: changes before gathering additional products data
 */
function fn_product_variations_gather_additional_products_data_params($product_ids, $params, &$products, $auth, $products_images, $additional_images, $product_options, &$has_product_options, $has_product_options_links)
{
    $loader = new AdditionalDataLoader(
        $products, $params, $auth, CART_LANGUAGE, Tygh::$app['addons.product_variations.product.manager'], Tygh::$app['db']
    );

    Registry::set('product_variations_loader', $loader);

    $default_product_data = reset($products);

    foreach ($products as &$product) {
        if (!isset($product['product_type'])) {
            $product['product_type'] = null;
        }

        if ($product['product_type'] === ProductManager::PRODUCT_TYPE_CONFIGURABLE && $params['get_options']) {
            $has_product_options = $product_options;
            if (!empty($product['selected_options']) && !empty($product_options[$product['product_id']])
                && !array_diff_key($product['selected_options'], $product_options[$product['product_id']])
            ){
                $has_product_options = array();
            }

            $options = isset($product_options[$product['product_id']]) ? $product_options[$product['product_id']] : array();
            $product = $loader->setOptions($product, $options);
        }

        if (!empty($product['is_default_variation']) && $product['is_default_variation'] == 'Y') {
            $default_product_data = $product;
        }
    }

    if ($default_product_data['product_type'] === ProductManager::PRODUCT_TYPE_VARIATION) {
        $products[$default_product_data['product_id']]['is_default_variation'] = 'Y';
    }
}

/**
 * Hook handler: changes before gathering product options.
 */
function fn_product_variations_gather_additional_product_data_before_options(&$product, $auth, &$params)
{
    if ($product['product_type'] === ProductManager::PRODUCT_TYPE_CONFIGURABLE) {
        $params['get_options'] = false;
        /** @var AdditionalDataLoader $loader */
        $loader = Registry::get('product_variations_loader');

        $product = $loader->loadBaseData($product);
    }
}

/**
 * Hook handler: add additional data to product
 */
function fn_product_variations_gather_additional_product_data_post(&$product, $auth, &$params)
{
    /** @var AdditionalDataLoader $loader */
    $loader = Registry::get('product_variations_loader');

    if ($product['product_type'] === ProductManager::PRODUCT_TYPE_CONFIGURABLE
        && ($params['get_features'] || isset($product['detailed_params']['info_type']) && $product['detailed_params']['info_type'] === 'D')
    ) {
        $product = $loader->loadFeatures($product);
    }

    $base_params = $loader->getParams();
    $params['get_options'] = $base_params['get_options'];

    if (isset($product['detailed_params'])) {
        $product['detailed_params']['get_options'] = $params['get_options'];
    }
}

/**
 * Hook handler: on before product features saved.
 */
function fn_product_variations_update_product_features_value_pre($product_id, $product_features, $add_new_variant, $lang_code, $params, &$category_ids)
{
    /** @var ProductManager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    $product_type = $product_manager->getProductFieldValue($product_id, 'product_type');

    if ($product_type === ProductManager::PRODUCT_TYPE_VARIATION) {
        $parent_product_id = $product_manager->getProductFieldValue($product_id, 'parent_product_id');

        $id_paths = db_get_fields(
            'SELECT ?:categories.id_path FROM ?:products_categories '
            . 'LEFT JOIN ?:categories ON ?:categories.category_id = ?:products_categories.category_id '
            . 'WHERE product_id = ?i',
            $parent_product_id
        );

        $category_ids = array_unique(explode('/', implode('/', $id_paths)));
    }
}

/**
 * Hook handler: on reorder product.
 */
function fn_product_variations_reorder_product($order_info, $cart, $auth, $product, $amount, &$price, $zero_price_action)
{
    /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];
    $product_id = $product['product_id'];

    $product_type = isset($product['product_type']) ? $product['product_type'] : $product_manager->getProductFieldValue($product_id, 'product_type');

    if ($product_type === ProductManager::PRODUCT_TYPE_CONFIGURABLE) {
        $variation_id = $product_manager->getVariationId($product_id, (array) $product['product_options']);

        if ($variation_id) {
            $price = fn_get_product_price($variation_id, $amount, $auth);
        }
    }
}

/**
 * Hook handler: on update product.
 */
function fn_product_variations_update_product_pre(&$product_data, $product_id, $lang_code, &$can_update)
{
    /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];
    $current_product_data = $parent_product_data = array();
    $can_update = true;

    if (!empty($product_id)) {
        $current_product_data = db_get_row('SELECT * FROM ?:products WHERE product_id = ?i', $product_id);
    }

    if (isset($product_data['parent_product_id'])) {
        $parent_product_id = (int) $product_data['parent_product_id'];

    } elseif (!empty($current_product_data)) {
        $product_data['parent_product_id'] = $parent_product_id = (int) $current_product_data['parent_product_id'];
    }

    if (!empty($parent_product_id)) {
        $parent_product_data = db_get_row('SELECT * FROM ?:products WHERE product_id = ?i', $parent_product_id);
    }

    $product_type = null;

    if (isset($product_data['product_type'])) {
        $product_type = $product_data['product_type'];
    } elseif (!empty($current_product_data)) {
        $product_type = $current_product_data['product_type'];
    }

    if ($product_type === ProductManager::PRODUCT_TYPE_VARIATION) {
        $current_variation_options = isset($current_product_data['variation_options']) ? json_decode($current_product_data['variation_options'], true) : null;
        $variation_options = isset($product_data['variation_options']) ? $product_data['variation_options'] : null;

        if (!$variation_options
            && isset($product_data['variation_code'])
            && $product_data['variation_code'] != $current_product_data['variation_code']
        ) {
            $variation_options = $product_manager->getSelectedOptionsByVariationCode($product_data['variation_code']);

            if (empty($variation_options)) {
                fn_set_notification('E', __('error'), __('product_variations.cannot_change_variation_options'));
            }
        }

        if (empty($parent_product_data)) {
            fn_set_notification('E', __('error'), __('product_variations.error.product_variation_must_have_parent_product'));
            $can_update = false;
            return;
        }

        if (empty($product_id) && empty($variation_options)) {
            fn_set_notification('E', __('error'), __('product_variations.error.product_variation_must_have_variation_options'));
            $can_update = false;
            return;
        } elseif ($variation_options && $variation_options != $current_variation_options) {
            $variant_ids = array_values($variation_options);
            $option_ids = array_keys($variation_options);
            $variation_code = $product_manager->getVariationCode($parent_product_data['product_id'], $variation_options);

            $cnt = db_get_field(
                'SELECT COUNT(*) AS cnt FROM ?:product_option_variants WHERE variant_id IN (?n) AND option_id IN (?n)',
                $variant_ids,
                $option_ids
            );

            if ($cnt != count($variant_ids)) {
                fn_set_notification('E', __('error'), __('product_variations.error.invalid_variation_options_array'));
                $can_update = false;
                return;
            }

            $exist_variation_product_id = db_get_field('SELECT product_id FROM ?:products WHERE variation_code = ?s', $variation_code);

            if ($exist_variation_product_id) {
                fn_set_notification('E', __('error'), __('product_variations.error.product_variation_already_exists'));
                $can_update = false;
                return;
            }

            $product_data['variation_code'] = $variation_code;
            $product_data['company_id'] = $parent_product_data['company_id'];
        }

        if ($variation_options) {
            $product_data['variation_options'] = json_encode($variation_options);
        }

    } elseif ($product_type === ProductManager::PRODUCT_TYPE_CONFIGURABLE) {
        if (!empty($product_data['tracking'])) {
            $product_data['tracking'] = $product_manager->normalizeTracking($product_data['tracking']);
        }

        if (!empty($product_data['prices'])) {
            $product_manager->updateProductVariationsPrices(
                $product_id,
                $product_data['price'],
                $product_data['prices'],
                Tygh::$app['session']['auth']
            );
        }
    }
}

/**
 * Hook handler: on applying product options rules
 */
function fn_product_variations_apply_options_rules_post(&$product)
{
    if ($product['product_type'] === ProductManager::PRODUCT_TYPE_CONFIGURABLE) {
        $product['options_update'] = true;
    }
}

/**
 * Hook handler: on gets product code.
 */
function fn_product_variations_get_product_code($product_id, $selected_options, &$product_code)
{
    /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    $product_type = $product_manager->getProductFieldValue($product_id, 'product_type');

    if ($product_type === ProductManager::PRODUCT_TYPE_CONFIGURABLE) {
        $variation_id = $product_manager->getVariationId($product_id, $selected_options);

        if ($variation_id) {
            $product_code = $product_manager->getProductFieldValue($variation_id, 'product_code');
        }
    }
}

/**
 * Hook handler: on before gets product data on add product to cart.
 */
function fn_product_variations_pre_get_cart_product_data($hash, $product, $skip_promotion, $cart, $auth, $promotion_amount, &$fields, $join)
{
    $fields[] = '?:products.product_type';
    $fields[] = '?:products.variation_options';
}

/**
 * Hook handler: on gets product data on add product to cart.
 */
function fn_product_variations_get_cart_product_data($product_id, &$_pdata, $product, $auth, &$cart, $hash)
{
    $cart['products'][$hash]['product_type'] = $_pdata['product_type'];

    if ($_pdata['product_type'] !== ProductManager::PRODUCT_TYPE_CONFIGURABLE) {
        return;
    }

    /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    $selected_options = isset($product['product_options']) ? $product['product_options'] : array();
    $amount = !empty($product['amount_total']) ? $product['amount_total'] : $product['amount'];

    $variation_id = $product_manager->getVariationId($product_id, $selected_options);

    if ($variation_id) {
        $product_type_instance = $product_manager->getProductTypeInstance($_pdata['product_type']);

        $_pdata['price'] = fn_get_product_price($variation_id, $amount, $auth);
        $_pdata['in_stock'] = $product_manager->getProductFieldValue($variation_id, 'amount');

        if (!isset($_pdata['extra'])) {
            $_pdata['extra'] = array();
        }

        $_pdata['extra']['variation_product_id'] = $variation_id;

        if (!isset($product['stored_price']) || $product['stored_price'] !== 'Y') {
            $_pdata['base_price'] = $_pdata['price'];
        }

        foreach ($_pdata as $key => $value) {
            if ($product_type_instance->isFieldMergeable($key) && $key !== 'amount') {
                $_pdata[$key] = $product_manager->getProductFieldValue($variation_id, $key);
            }
        }
    }
}

/**
 * Hook handler: on update product quantity.
 */
function fn_product_variations_update_product_amount_pre(&$product_id, $amount, $product_options, $sign, &$tracking, &$current_amount, &$product_code)
{
    /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    $product_type = $product_manager->getProductFieldValue($product_id, 'product_type');

    if ($product_type === ProductManager::PRODUCT_TYPE_CONFIGURABLE) {
        $variation_id = $product_manager->getVariationId($product_id, $product_options);

        if ($variation_id) {
            $product_id = $variation_id;
            $current_amount  = $product_manager->getProductFieldValue($variation_id, 'amount', null, true);
            $product_code  = $product_manager->getProductFieldValue($variation_id, 'product_code');
            $tracking = ProductTracking::TRACK_WITHOUT_OPTIONS;
        }
    }
}

/**
 * Hook handler: on checks product quantity in stock.
 */
function fn_product_variations_check_amount_in_stock_before_check($product_id, $amount, $product_options, $cart_id, $is_edp, $original_amount, $cart, $update_id, &$product, &$current_amount)
{
    if (
        (isset($product['tracking'])
            && $product['tracking'] === ProductTracking::DO_NOT_TRACK)
        || Registry::get('settings.General.inventory_tracking') == 'N'
    ) {
        return;
    }

    /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    $product_type = $product_manager->getProductFieldValue($product_id, 'product_type');

    if ($product_type === ProductManager::PRODUCT_TYPE_CONFIGURABLE) {
        $product_type_instance = $product_manager->getProductTypeInstance($product_type);
        $variation_id = $product_manager->getVariationId($product_id, $product_options);

        if ($variation_id) {
            $current_amount = $product_manager->getProductFieldValue($variation_id, 'amount');
            $avail_since = $product_manager->getProductFieldValue($variation_id, 'avail_since');

            if (!empty($avail_since) && TIME < $avail_since) {
                $current_amount = 0;
            }

            foreach ($product as $key => $value) {
                if ($product_type_instance->isFieldMergeable($key)) {
                    $product[$key] = $product_manager->getProductFieldValue($variation_id, $key);
                }
            }

            if (!empty($cart['products']) && is_array($cart['products'])) {
                foreach ($cart['products'] as $key => $item) {
                    if ($key != $cart_id && $item['product_id'] == $product_id) {
                        if (isset($item['extra']['variation_product_id'])) {
                            $item_variation_id = $item['extra']['variation_product_id'];
                        } else {
                            $item_variation_id = $product_manager->getVariationId($product_id, $item['product_options']);
                        }

                        if ($item_variation_id == $variation_id) {
                            $current_amount -= $item['amount'];
                        }
                    }
                }
            }
        }
    }
}

/**
 * Hook handler: on checks product price on add product to cart.
 */
function fn_product_variations_add_product_to_cart_get_price($product_data, $cart, $auth, $update, $_id, &$data, $product_id, $amount, &$price, $zero_price_action, $allow_add)
{
    /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    $product_type = $product_manager->getProductFieldValue($product_id, 'product_type');
    $data['extra']['product_type'] = $product_type;

    if ($product_type === ProductManager::PRODUCT_TYPE_CONFIGURABLE) {
        $variation_id = $product_manager->getVariationId($product_id, $data['product_options']);

        if ($variation_id) {
            $data['extra']['variation_product_id'] = $variation_id;
            $price = fn_get_product_price($variation_id, $amount, $auth);
        }
    }
}

/**
 * Hook handler: on add product to cart.
 */
function fn_product_variations_add_to_cart(&$cart, $product_id, $_id)
{
    /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    $product_type = $product_manager->getProductFieldValue($product_id, 'product_type');
    $cart['products'][$_id]['product_type'] = $product_type;
}

/**
 * Hook handler: on gets product image pairs.
 */
function fn_product_variations_get_cart_product_icon($product_id, $product_data, $selected_options, &$image)
{
    if (!empty($selected_options)) {
        /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
        $product_manager = Tygh::$app['addons.product_variations.product.manager'];

        $product_type = $product_manager->getProductFieldValue($product_id, 'product_type');

        if ($product_type === ProductManager::PRODUCT_TYPE_CONFIGURABLE) {
            $variation_id = $product_manager->getVariationId($product_id, $selected_options);
            $variation_image = fn_get_image_pairs($variation_id, 'product', 'M', true, true);

            if (!empty($variation_image)) {
                $image = $variation_image;
            }
        }
    }
}

/**
 * Hook handler: on creates order details.
 */
function fn_product_variations_create_order_details($order_id, $cart, &$order_details, $extra)
{
    if (!empty($extra['product_options'])) {
        /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
        $product_manager = Tygh::$app['addons.product_variations.product.manager'];

        $product_id = $order_details['product_id'];
        $product_type = $product_manager->getProductFieldValue($product_id, 'product_type');
        $extra['product_type'] = $product_type;

        if ($product_type === ProductManager::PRODUCT_TYPE_CONFIGURABLE) {
            $order_details['product_code'] = fn_get_product_code($product_id, $extra['product_options']);
        }

        $order_details['extra'] = serialize($extra);
    }
}

/**
 * Hook handler: on data feed export.
 */
function fn_product_variations_data_feeds_export($datafeed_id, &$options, &$pattern, $fields, $datafeed_data)
{
    if (!empty($datafeed_data['export_options']['product_types'])) {
        $product_types = $datafeed_data['export_options']['product_types'];
        $pattern['product_types'] = $product_types;

        if (in_array(ProductManager::PRODUCT_TYPE_VARIATION, $product_types)
            && !in_array(ProductManager::PRODUCT_TYPE_CONFIGURABLE, $product_types)
        ) {
            $product_types[] = ProductManager::PRODUCT_TYPE_CONFIGURABLE;
        }

        $pattern['condition']['conditions']['product_type'] = $product_types;
    } else {
        $pattern['product_types'] = array();
    }

    unset($options['product_types']);
}


/**
 * Hook handler: on before dispatch displayed
 */
function fn_product_variations_dispatch_before_display()
{
    $controller = Registry::get('runtime.controller');
    $mode = Registry::get('runtime.mode');

    if (AREA !== 'A' || $controller !== 'products' || $mode !== 'update') {
        return;
    }

    /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];
    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];

    /** @var array $product_data */
    $product_data = $view->getTemplateVars('product_data');

    $product_type = $product_manager->getProductTypeInstance($product_data['product_type']);
    $tabs = Registry::get('navigation.tabs');

    if (is_array($tabs)) {

        foreach ($tabs as $key => $tab) {
            if (!$product_type->isTabAvailable($key)) {
                unset($tabs[$key]);
            }
        }

        Registry::set('navigation.tabs', $tabs);
    }
}

/**
 * Hook handler: on update cart products
 */
function fn_product_variations_update_cart_products_post(&$cart)
{
    foreach ($cart['products'] as &$product) {
        if (!empty($product['product_options'])) {
            /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
            $product_manager = Tygh::$app['addons.product_variations.product.manager'];
            $product_type = $product_manager->getProductFieldValue($product['product_id'], 'product_type');
            $product['extra']['product_type'] = $product_type;

            if ($product_type === ProductManager::PRODUCT_TYPE_CONFIGURABLE) {
                $variation_id = $product_manager->getVariationId($product['product_id'], $product['product_options']);

                if ($variation_id) {
                    $product['extra']['variation_product_id'] = $variation_id;
                }
            }
        }
    }
}

/**
 * Hook handler: after the license agreements of the files of downloadable products are retrieved.
 * Substitutes the license agreements of configurable products with the license agreements of selected variations.
 */
function fn_product_variations_cart_agreements($cart, &$agreements)
{
    if (!empty($cart['products'])) {
        foreach ($cart['products'] as $item) {
            if ($item['is_edp'] === 'Y' && !empty($item['extra']['variation_product_id'])) {
                $variation_product_id = $item['extra']['variation_product_id'];

                if ($variation_agreements = fn_get_edp_agreements($variation_product_id, true)) {
                    unset($agreements[$item['product_id']]);
                    $agreements[$variation_product_id] = $variation_agreements;
                }
            }
        }
    }
}

/**
 * Hook handler: after generating ekeys for downloadable products (EDP)
 * Generates ekeys for the downloadable files of the variation.
 */
function fn_product_variations_generate_ekeys_for_edp_post($statuses, $order_info, $active_files, &$edp_data)
{
    $parent_product_ids = array();
    $products = $order_info['products'];
    $order_info['products'] = array();

    foreach ($products as $key => $product) {
        if (!empty($product['extra']['is_edp'])
            && $product['extra']['is_edp'] == 'Y'
            && !empty($product['extra']['variation_product_id'])
        ) {
            $variation_id = $product['extra']['variation_product_id'];
            $product_id = $product['product_id'];

            $cnt = db_get_field('SELECT COUNT(*) FROM ?:product_files WHERE product_id = ?i', $variation_id);

            if ($cnt) {
                unset($product['extra']['variation_product_id']);
                $parent_product_ids[$variation_id] = $product_id;
                $product['product_id'] = $variation_id;

                $order_info['products'][$key] = $product;

                if (isset($active_files[$product_id])) {
                    $active_files[$variation_id] = $active_files[$product_id];
                }
            }
        }
    }

    if (!empty($order_info['products'])) {
        $data = fn_generate_ekeys_for_edp($statuses, $order_info, $active_files);

        foreach ($data as $variation_id => $item) {
            $parent_product_id = $parent_product_ids[$variation_id];
            unset($edp_data[$parent_product_id]);

            $edp_data[$variation_id] = $item;
        }
    }
}

/**
 * Hook handler: after getting the list of downloadable products available for the user.
 * Substitutes the list of downloadable files of configurable products with the downloadable files of selected variations.
 */
function fn_product_variations_get_user_edp_post($params, $items_per_page, &$products)
{
    $order_ids = array();
    $products_folders = $products_files = $products_files_tree = array();

    foreach ($products as $item) {
        $order_ids[$item['order_id']] = $item['order_id'];
    }

    $orders_products = db_get_array(
        'SELECT * FROM ?:order_details WHERE order_id IN (?n)',
        $order_ids
    );

    foreach ($orders_products as $item) {
        $product_id = $item['product_id'];
        $extra = @unserialize($item['extra']);

        if (!empty($extra['variation_product_id'])) {

            $filter = array (
                'product_id' => $extra['variation_product_id'],
                'order_id' => $item['order_id']
            );

            list($folders) = fn_get_product_file_folders($filter);
            list($files) = fn_get_product_files($filter);

            if (isset($products_folders[$product_id][$item['order_id']])) {
                $products_folders[$product_id][$item['order_id']] = array_merge($products_folders[$product_id][$item['order_id']], $folders);
            } else {
                $products_folders[$product_id][$item['order_id']] = $folders;
            }

            if (isset($products_files[$product_id][$item['order_id']])) {
                $products_files[$product_id][$item['order_id']] = array_merge($products_files[$product_id][$item['order_id']],  $files);
            } else {
                $products_files[$product_id][$item['order_id']] = $files;
            }
        }
    }

    foreach ($products_files as $product_id => $order_ids) {
        foreach ($order_ids as $order_id => $item) {
            $products_files_tree[$product_id][$order_id] = fn_build_files_tree($products_folders[$product_id][$order_id], $item);
        }
    }

    foreach ($products as &$item) {
        $product_id = $item['product_id'];
        $order_id = $item['order_id'];

        if (!empty($products_files_tree[$product_id][$order_id])) {
            $item['files_tree'] = $products_files_tree[$product_id][$order_id];
        }
    }

    unset($item);
}


/**
 * Hook handler: after getting the order data.
 * Substitutes the list of downloadable files of configurable products with the downloadable files of selected
 * variations.
 */
function fn_product_variations_get_order_info(&$order, $additional_data)
{
    if (!$order) {
        return;
    }

    foreach ($order['products'] as &$product) {
        if (isset($product['extra']['is_edp'])
            && $product['extra']['is_edp']
            && !empty($product['extra']['variation_product_id'])
        ) {
            list($product['files']) = fn_get_product_files(
                array(
                    'product_id' => $product['extra']['variation_product_id'],
                    'order_id' => $order['order_id']
                )
            );
        }
    }
    unset($product);
}

/**
 * Hook handler: before getting product files.
 */
function fn_product_variations_get_product_files_before_select($params, &$fields, $join, $condition)
{
    if (!empty($params['order_id'])) {
        $fields[] = '?:product_file_ekeys.ttl';
    }
}

/**
 * Hook handler: after a file of a downloadable product is added or updated.
 * Adds the file uploaded for a variation to its parent configurable product, but only if the parent product doesn't have active files.
 */
function fn_product_variations_update_product_file_post($product_file, $file_id, $lang_code)
{
    /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];
    $product_id = $product_file['product_id'];

    $product_type = $product_manager->getProductFieldValue($product_id, 'product_type');

    if ($product_type === ProductManager::PRODUCT_TYPE_VARIATION) {
        $parent_product_id = $product_manager->getProductFieldValue($product_id, 'parent_product_id');

        $cnt = fn_product_variations_get_product_files_count($parent_product_id, 'A');

        if (empty($cnt)) {
            /** @var \Tygh\Backend\Storage\ABackend $storage */
            $storage = Storage::instance('downloads');

            unset($product_file['file_id']);
            $product_file['product_id'] = $parent_product_id;
            $product_file['folder_id'] = null;

            $data = db_get_row('SELECT * FROM ?:product_files WHERE file_id = ?i', $file_id);

            $file_id = fn_update_product_file($product_file, 0, $lang_code);

            if (!empty($data['file_path'])) {
                $file_name = $parent_product_id . '/' . $data['file_path'];

                if ($storage->isExist($file_name)) {
                    $file_name = $storage->generateName($file_name);
                }

                if ($storage->copy($product_id . '/' . $data['file_path'], $file_name)) {
                    db_query(
                        'UPDATE ?:product_files SET ?u WHERE file_id = ?i',
                        array(
                            'file_path' => fn_basename($file_name),
                            'file_size' => $data['file_size']
                        ),
                        $file_id
                    );
                }
            }

            if (!empty($data['preview_path'])) {
                $file_name = $parent_product_id . '/' . $data['preview_path'];

                if ($storage->isExist($file_name)) {
                    $file_name = $storage->generateName($file_name);
                }

                if ($storage->copy($product_id . '/' . $data['preview_path'], $file_name)) {
                    db_query(
                        'UPDATE ?:product_files SET ?u WHERE file_id = ?i',
                        array(
                            'preview_path' => fn_basename($file_name),
                            'preview_size' => $data['preview_size']
                        ),
                        $file_id
                    );
                }
            }
        }
    }
}

/**
 * Hook handler: after options reselected
 */
function fn_product_variations_after_options_calculation($mode, $data)
{
    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];

    /** @var array $product */
    $product = $view->getTemplateVars('product');

    if (!empty($product['product_type'])
        && $product['product_type'] === ProductManager::PRODUCT_TYPE_CONFIGURABLE
    ) {
        $variation_id = $product['variation_product_id'];

        $params = array (
            'product_id' => $variation_id,
            'preview_check' => true
        );

        // get files for variation
        list($files) = fn_get_product_files($params);

        if (!empty($files)) {
            Tygh::$app['view']->assign('files', $files);
        }

        fn_init_product_tabs($product);

        // check if product option change happened not in block
        if (empty($data['appearance']['obj_prefix'])) {
            $view->assign('no_capture', false);
        }
    }
}

/**
 * Hook handler: on after update product quantity.
 */
function fn_product_variations_update_product_amount_post($product_id)
{
    /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    $product_type = $product_manager->getProductFieldValue($product_id, 'product_type');

    if ($product_type === ProductManager::PRODUCT_TYPE_VARIATION) {
        $parent_product_id = $product_manager->getProductFieldValue($product_id, 'parent_product_id');
        $product_manager->actualizeConfigurableProductAmount((array) $parent_product_id);
    }
}
/**
 * Hook handler: on after update product.
 */
function fn_product_variations_update_product_post($product_data, $product_id)
{
    if (isset($product_data['amount'])) {
        /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
        $product_manager = Tygh::$app['addons.product_variations.product.manager'];
        $product_type = $product_manager->getProductFieldValue($product_id, 'product_type');

        if ($product_type === ProductManager::PRODUCT_TYPE_VARIATION) {
            $parent_product_id = $product_manager->getProductFieldValue($product_id, 'parent_product_id');
            $product_manager->actualizeConfigurableProductAmount((array) $parent_product_id);
        }
    }
}

/**
 * Hook handler: on before delete product.
 */
function fn_product_variations_delete_product_pre($product_id)
{
    /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    $product_type = $product_manager->getProductFieldValue($product_id, 'product_type');

    if ($product_type === ProductManager::PRODUCT_TYPE_VARIATION) {
        $parent_product_id = $product_manager->getProductFieldValue($product_id, 'parent_product_id');
        Registry::set('runtime.product_variations.update_product_id', $parent_product_id);

        $is_default_variation = $product_manager->getProductFieldValue($product_id, 'is_default_variation');
        if ($is_default_variation != 'Y') {
            return;
        }

        $variation_product_ids = $product_manager->getProductVariations($parent_product_id);
        foreach ($variation_product_ids as $variation_product_id) {
            if ($product_id != $variation_product_id) {
                $product_manager->updateDefaultVariation($variation_product_id);

                return;
            }
        }

    } else {
        Registry::del('runtime.product_variations.update_product_id');
    }
}

/**
 * Hook handler: on after delete product.
 */
function fn_product_variations_delete_product_post()
{
    $product_id = Registry::get('runtime.product_variations.update_product_id');

    if ($product_id) {
        /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
        $product_manager = Tygh::$app['addons.product_variations.product.manager'];
        $product_manager->actualizeConfigurableProductAmount((array) $product_id);
    }
}

/**
 * Hook handler: on after change status.
 */
function fn_product_variations_tools_change_status($params, $result)
{
    if ($result
        && !empty($params['table'])
        && !empty($params['id'])
        && $params['table'] === 'products'
    ) {
        /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
        $product_manager = Tygh::$app['addons.product_variations.product.manager'];

        $product_type = $product_manager->getProductFieldValue($params['id'], 'product_type');

        if ($product_type === ProductManager::PRODUCT_TYPE_VARIATION) {
            $parent_product_id = $product_manager->getProductFieldValue($params['id'], 'parent_product_id');
            $product_manager->actualizeConfigurableProductAmount((array) $parent_product_id);
        }
    }
}

/**
 * Hook handler: on before getting product data.
 */
function fn_product_variations_get_product_data_pre(&$product_id)
{
    if (AREA !== 'C') {
        return;
    }

    Registry::del('runtime.selected_options.' . $product_id);

    /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    $product_type = $product_manager->getProductFieldValue($product_id, 'product_type');

    if ($product_type === ProductManager::PRODUCT_TYPE_VARIATION) {
        $selected_options = $product_manager->getProductVariationOptionsValue($product_id);
        $product_id = $product_manager->getProductFieldValue($product_id, 'parent_product_id');

        Registry::set('runtime.selected_options.' . $product_id, $selected_options);
    }
}

/**
 * Hook handler: before loading product product data.
 */
function fn_product_variations_load_products_extra_data(&$extra_fields, $products, $product_ids, &$params, $lang_code)
{
    if (AREA === 'C'
        && !empty($params['product_type'])
        && is_array($params['product_type'])
        && count($params['product_type']) === 1
    ) {
        $product_type = array_pop($params['product_type']);

        if ($product_type !== ProductManager::PRODUCT_TYPE_VARIATION) {
            return;
        }

        if (!empty($params['extend']) && is_array($params['extend'])) {
            $product_name_key = array_search('product_name', $params['extend']);
            unset($params['extend'][$product_name_key]);
        }

        unset(
            $extra_fields['?:product_descriptions'],
            $extra_fields['?:products_categories']
        );
    }
}

/**
 * Hook handler: before executing deleting option query
 */
function fn_product_variations_delete_product_option_before_delete($option_id, $pid, $product_id, $product_link, &$can_continue)
{
    $result = fn_product_variations_can_delete_product_option($option_id);
    
    if (!$result->isSuccess()) {
        $can_continue = false;
        $result->showNotifications();
    }
}

/**
 * Checks can disable product option if it linked to any product variation.
 *
 * @param int    $option_id Option identifier
 *
 * @return \Tygh\Common\OperationResult
 */
function fn_product_variations_can_disable_product_option($option_id)
{
    return fn_product_variations_can_process_product_option($option_id, 'disable');
}

/**
 * Checks can delete product option if it linked to any product variation.
 *
 * @param int    $option_id Option identifier
 *
 * @return \Tygh\Common\OperationResult
 */
function fn_product_variations_can_delete_product_option($option_id)
{
    return fn_product_variations_can_process_product_option($option_id, 'remove');
}

/**
 * Checks can process product option if it linked to any product variation.
 *
 * @param int    $option_id Option identifier
 * @param string $action    Action 
 *
 * @return \Tygh\Common\OperationResult
 * @internal
 */
function fn_product_variations_can_process_product_option($option_id, $action)
{
    /** @var \Tygh\Common\OperationResult $result */
    $result = new OperationResult();

    $product_id = db_get_field('SELECT product_id FROM ?:product_options WHERE option_id = ?i', $option_id);

    if ($product_id) {
        // we deleting options from the product itself
        $product_ids = array($product_id);
    } else {
        // we deleting global option itself
        $product_ids = db_get_fields('SELECT product_id FROM ?:product_global_option_links WHERE option_id = ?i', $option_id);
    }

    if ($product_ids) {
        $variation_products = db_get_array(
            'SELECT parent_product_id, variation_options FROM ?:products WHERE parent_product_id IN (?n) AND product_type = ?s',
            $product_ids,
            ProductManager::PRODUCT_TYPE_VARIATION
        );

        if (is_array($variation_products)) {
            /** @var ProductManager $product_manager */
            $product_manager = Tygh::$app['addons.product_variations.product.manager'];
            $parent_product_ids = array();

            foreach ($variation_products as $product) {

                if (in_array($product['parent_product_id'], $parent_product_ids)) {
                    continue;
                }

                $options = $product_manager->decodeVariationOptions($product['variation_options']);

                if (isset($options[$option_id])) {
                    $parent_product_ids[] = $product['parent_product_id'];
                }
            }

            if ($parent_product_ids) {
                $option_name = db_get_field(
                    'SELECT option_name FROM ?:product_options_descriptions WHERE option_id = ?i AND lang_code = ?s',
                    $option_id,
                    CART_LANGUAGE
                );                
                $result->addError(
                    'unable_to_' . $action . '_options',
                    __('product_variations.cannot_' . $action . '_options_that_used_for_variation',
                        array(
                            '[option_name]' => $option_name,
                            '[search_link]' => fn_url(
                                sprintf(
                                    'products.manage&is_search=Y&product_type=%s&parent_product_id=%s',
                                    ProductManager::PRODUCT_TYPE_VARIATION,
                                    implode(',', $parent_product_ids)
                                )
                            )
                        )
                    )
                );
            }
        }
    }

    $result->setSuccess(!$result->hasErrors());
    return $result;
}

/**
 * Converts product to configurable product.
 *
 * @param int $product_id Product identifier
 */
function fn_product_variations_convert_to_configurable_product($product_id)
{
    /** @var ProductManager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    $auth = array();

    $product = fn_get_product_data($product_id, $auth);
    $languages = Languages::getAll();
    $product_options = fn_get_product_options($product_id, CART_LANGUAGE, true);
    $product_row = db_get_row('SELECT * FROM ?:products WHERE product_id = ?i', $product_id);
    $product_variation_ids = db_get_fields('SELECT product_id FROM ?:products  WHERE parent_product_id = ?i', $product_id);
    $product_exceptions = fn_get_product_exceptions($product_id);

    foreach ($product_variation_ids as $product_variation_id) {
        fn_delete_product($product_variation_id);
    }

    $options_ids = array();
    $inventory_combinations = db_get_array('SELECT * FROM ?:product_options_inventory WHERE product_id = ?i', $product_id);
    $index = 0;
    $default_product_variation = $product_manager->getDefaultVariationOptions($product_id);

    foreach ($inventory_combinations as $item) {
        $index++;
        $selected_options = array();
        $parts = array_chunk(explode('_', $item['combination']), 2);

        foreach ($parts as $part) {
            $selected_options[$part[0]] = $part[1];
        }

        $combination = fn_product_variations_get_variation_by_selected_options(
            $product,
            $product_options,
            $selected_options,
            $index
        );

        if (!empty($item['product_code'])) {
            $combination['code'] = $item['product_code'];
        }

        if (isset($item['amount'])) {
            $combination['amount'] = $item['amount'];
        }

        $is_allow = true;

        if ($product_row['exceptions_type'] == 'F') {
            foreach ($product_exceptions as $exception) {

                foreach ($exception['combination'] as $option_id => &$variant_id) {
                    if ($variant_id == OPTION_EXCEPTION_VARIANT_ANY || $variant_id == OPTION_EXCEPTION_VARIANT_NOTHING) {
                        $variant_id = isset($combination['selected_options'][$option_id]) ? $combination['selected_options'][$option_id] : null;
                    }
                }
                unset($variant_id);

                if ($exception['combination'] == $combination['selected_options']) {
                    $is_allow = false;
                    break;
                }
            }
        } elseif ($product_row['exceptions_type'] == 'A') {
            $is_allow = false;

            foreach ($product_exceptions as $exception) {

                foreach ($exception['combination'] as $option_id => &$variant_id) {
                    if ($variant_id == OPTION_EXCEPTION_VARIANT_ANY) {
                        $variant_id = isset($combination['selected_options'][$option_id]) ? $combination['selected_options'][$option_id] : null;
                    }
                }
                unset($variant_id);

                if ($exception['combination'] == $combination['selected_options']) {
                    $is_allow = true;
                    break;
                }
            }
        }

        if (!$is_allow) {
            continue;
        }

        $combination['is_default_variation'] = ($default_product_variation) ? 'N' : 'Y';
        $variation_id = fn_product_variations_save_variation($product_row, $combination, $languages);

        $default_product_variation = true;

        $image = fn_get_image_pairs($item['combination_hash'], 'product_option', 'M', true, true);

        if ($image) {
            $detailed = $icons = array();
            $pair_data = array(
                'type' => 'M',
            );

            if (!empty($image['icon'])) {
                $tmp_name = fn_create_temp_file();
                Storage::instance('images')->export($image['icon']['relative_path'], $tmp_name);
                $name = fn_basename($image['icon']['image_path']);

                $icons[$image['pair_id']] = array(
                    'path'  => $tmp_name,
                    'size'  => filesize($tmp_name),
                    'error' => 0,
                    'name'  => $name,
                );

                $pair_data['image_alt'] = empty($image['icon']['alt']) ? '' : $image['icon']['alt'];
            }

            if (!empty($image['detailed'])) {
                $tmp_name = fn_create_temp_file();
                Storage::instance('images')->export($image['detailed']['relative_path'], $tmp_name);
                $name = fn_basename($image['detailed']['image_path']);

                $detailed[$image['pair_id']] = array(
                    'path'  => $tmp_name,
                    'size'  => filesize($tmp_name),
                    'error' => 0,
                    'name'  => $name,
                );

                $pair_data['detailed_alt'] = empty($image['detailed']['alt']) ? '' : $image['detailed']['alt'];
            }

            $pairs_data = array(
                $image['pair_id'] => $pair_data,
            );

            fn_update_image_pairs($icons, $detailed, $pairs_data, $variation_id, 'product');
        }
    }

    if (!empty($selected_options)) {
        $options_ids = array_keys($selected_options);
    }

    $product_manager->changeProductTypeToConfigurable($product_id, array_values($options_ids));

    fn_delete_product_option_combinations($product_id);
    db_query('DELETE FROM ?:product_options_exceptions WHERE product_id = ?i', $product_id);
}

/**
 * Hook handler: Prepares options mapping data from configurable product for later use when cloning its variations
 */
function fn_product_variations_clone_product_options_post($from_product_id, $to_product_id, $from_global_option_id, $change_options, $change_variants)
{
    /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    if (!$product_manager->hasProductVariations($from_product_id)) {
        return;
    }

    $options = array();
    foreach ($change_options as $option_id => $clonned_option_id) {
        $options[$option_id] = array(
            'option_id' => $clonned_option_id,
            'variants'  => $change_variants,
        );
    }

    Registry::set('runtime.product_variations.product_clone.product_id', $to_product_id);
    Registry::set('runtime.product_variations.product_clone.options', $options);
}

/**
 * Hook handler: Clones a product with variations.
 */
function fn_product_variations_clone_product_post($product_id, $pid, $orig_name, $new_name)
{
    /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    if (!$product_manager->hasProductVariations($product_id)) {
        return;
    }

    if ($options = Registry::get('runtime.product_variations.product_clone.options')) {
        $original_product_variation_options = $product_manager->decodeVariationOptions(
            $product_manager->getProductFieldValue($product_id, 'variation_options')
        );

        $clonned_product_variation_options = array();

        foreach ($original_product_variation_options as $option_id) {
            $clonned_product_variation_options[] = $options[$option_id]['option_id'];
        }

        $product_manager->changeProductTypeToConfigurable($pid, $clonned_product_variation_options);
    }

    $product_ids = db_get_fields('SELECT product_id FROM ?:products WHERE parent_product_id = ?i', $product_id);

    if (empty($product_ids)) {
        return;
    }

    foreach ($product_ids as $product_id) {
        fn_clone_product($product_id);
    }
}

/**
 * Hook handler: Changes the shipping parameters for the product with variations.
 */
function fn_product_variations_get_cart_product_data_post($hash, $product, $skip_promotion, $cart, $auth, $promotion_amount, &$_pdata)
{
    if ($_pdata['product_type'] != ProductManager::PRODUCT_TYPE_CONFIGURABLE) {
        return;
    }

    $shipping_params = array_diff($_pdata['shipping_params'], array(0, null));

    if (empty($shipping_params)) {
        $parent_product_data = db_get_row('SELECT * FROM ?:products WHERE product_id = ?i', $_pdata['product_id']);

        $_pdata['shipping_params'] = empty($parent_product_data['shipping_params']) ? array() : unserialize($parent_product_data['shipping_params']);
        $_pdata['weight'] = empty($_pdata['weight']) ? $parent_product_data['weight'] : $_pdata['weight'];
    }
}

/**
 * Hook handler: Changes the package info for the product with variations.
 */
function fn_product_variations_shippings_get_package_info(&$group, $include_free_shipping, $package_info)
{
    foreach ($group['products'] as $cart_id => &$product) {
        if ($product['product_type'] != ProductManager::PRODUCT_TYPE_CONFIGURABLE) {
            continue;
        }

        if (!empty($product['extra']['variation_product_id'])) {
            $group['products'][$cart_id]['extra']['parent_product_id'] = $product['product_id'];
            $group['products'][$cart_id]['product_id'] = $product['extra']['variation_product_id'];
        }
    }
}

/**
 * Hook handler: Changes the data of a cloned variation.
 */
function fn_product_variations_clone_product_data($product_id, &$data, $is_cloning_allowed)
{
    /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    if ($data['product_type'] != ProductManager::PRODUCT_TYPE_VARIATION) {
        Registry::del('runtime.product_variations.product_clone');

        return;
    }

    if ($options = Registry::get('runtime.product_variations.product_clone.options')) {

        $select_options = $product_manager->getSelectedOptionsByVariationCode($data['variation_code']);

        foreach ($select_options as $option_id => $variant_id) {
            $variation_select_options[$options[$option_id]['option_id']] = $options[$option_id]['variants'][$variant_id];
        }

        $parent_product_id = Registry::get('runtime.product_variations.product_clone.product_id');
        $data['parent_product_id'] = $parent_product_id;
        $data['variation_code'] = $product_manager->getVariationCode(
            $parent_product_id,
            $variation_select_options
        );
        $data['variation_options'] = $product_manager->encodeVariationSelectedOptions($variation_select_options);
    }
}

/**
 * Hook handler: Changes the product data for the cart
 */
function fn_product_variations_pre_add_to_cart(&$product_data, $cart, $auth, $update)
{
    /** @var ProductManager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    $variation_product_data = array();
    foreach ($product_data as $variation_product_id => $data) {
        $product_type = $product_manager->getProductFieldValue($variation_product_id, 'product_type');

        if ($product_type == ProductManager::PRODUCT_TYPE_VARIATION) {
            $product_id = $product_manager->getProductFieldValue($variation_product_id, 'parent_product_id');
            $variation_product_data[$product_id] = $data;
            $variation_product_data[$product_id]['product_id'] = $product_id;
            $variation_product_data[$product_id]['product_options'] = $product_manager->getProductVariationOptionsValue($variation_product_id);
        }
    }

    $product_data = empty($variation_product_data) ? $product_data : $variation_product_data;
}

/*
 * Hook handler: Gets identifier for the product variations.
 */
function fn_product_variations_change_approval_status_pre(&$product_ids, $status)
{
    /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    $variations_product_ids = array();
    foreach ($product_ids as $product_id) {
        $variations_product_ids = $product_manager->getProductVariations($product_id);
    }

    $product_ids = array_merge($product_ids, $variations_product_ids);
}


/**
 * Hook handler: prevents removing and disabling option variant if it used for variations.
 */
function fn_product_variations_update_product_option_pre(&$option_data, $option_id, $lang_code)
{
    if (empty($option_id) || empty($option_data['variants'])) {
        return;
    }

    if (!empty($option_data['product_id'])) {
        $product_ids = array($option_data['product_id']);
        $option_product_id = $option_data['product_id'];
    } else {
        $product_ids = db_get_fields('SELECT product_id FROM ?:product_global_option_links WHERE option_id = ?i', $option_id);
        $option_product_id = 0;
    }

    if (empty($product_ids)) {
        return;
    }

    $current_option_data = fn_get_product_option_data($option_id, $option_product_id, $lang_code);

    if (empty($current_option_data['variants'])) {
        return;
    }

    $current_variant_ids = array_keys($current_option_data['variants']);
    $updated_variant_ids = $current_used_variant_ids = array();

    $variation_products = db_get_array(
        'SELECT parent_product_id, variation_options FROM ?:products WHERE parent_product_id IN (?n) AND product_type = ?s',
        $product_ids,
        ProductManager::PRODUCT_TYPE_VARIATION
    );

    if ($variation_products) {
        /** @var ProductManager $product_manager */
        $product_manager = Tygh::$app['addons.product_variations.product.manager'];

        foreach ($variation_products as $product) {
            $options = $product_manager->decodeVariationOptions($product['variation_options']);
            $current_used_variant_ids += array_combine($options, $options);
        }
    }

    foreach ($option_data['variants'] as &$variant) {
        if (empty($variant['variant_id'])) {
            continue;
        }

        $variant_id = $variant['variant_id'];

        if (
            isset($variant['status'])
            && $variant['status'] === 'D'
            && isset($current_used_variant_ids[$variant_id])
        ) {
            $variant['status'] = 'A';
            $variant_name = empty($variant['variant_name'])
                ? $current_option_data['variants'][$variant_id]['variant_name']
                : $variant['variant_name'];

            fn_set_notification('E', __('error'), __('product_variations.error.cannot_disable_option_variant', array(
                '[variant_name]' => $variant_name
            )));
        }

        $updated_variant_ids[] = $variant_id;
    }
    unset($variant);

    $deleted_variant_ids = array_intersect($current_used_variant_ids, array_diff($current_variant_ids, $updated_variant_ids));

    foreach ($deleted_variant_ids as $variant_id) {
        $variant = $current_option_data['variants'][$variant_id];

        $option_data['variants'][] = array(
            'variant_id' => $variant_id,
            'variant_name' => $variant['variant_name']
        );

        fn_set_notification('E', __('error'), __('product_variations.error.cannot_remove_option_variant', array(
            '[variant_name]' => $variant['variant_name']
        )));
    }
}

/**
 * Hook handler: adds status field to $extra_variant_fields
 */
function fn_product_variations_get_product_options($fields, $condition, $join, &$extra_variant_fields)
{
    if (AREA !== 'A') {
        return;
    }

    $extra_variant_fields .= 'a.status,';
}
