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
 * 'copyright.txt' FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

use Tygh\Addons\ProductVariations\Product\Manager as ProductManager;
use Tygh\Common\OperationResult;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

define('PRODUCT_VARIATIONS_EXIM_OPTION_AND_VARIANT_DELIMITER', ':');
define('PRODUCT_VARIATIONS_EXIM_OPTION_AND_VARIANT_ENCLOSURE', '"');
define('PRODUCT_VARIATIONS_EXIM_OPTIONS_DELIMITER', '|');
define('PRODUCT_VARIATIONS_EXIM_OPTIONS_ENCLOSURE', '/');

/**
 * Prepares product export. Saved conditions by product identifiers.
 *
 * @param array $pattern Exim schema.
 *
 * @return bool
 */
function fn_product_variations_exim_pre_moderation_by_product_type(&$pattern)
{
    if (!empty($pattern['condition']['conditions']['product_id'])) {
        $pattern['condition']['product_id'] = $pattern['condition']['conditions']['product_id'];
        unset($pattern['condition']['conditions']['product_id']);
    }

    return true;
}

/**
 * Prepares product export. Sets system field and sorting.
 *
 * @param array $pattern        Exim schema.
 * @param array $conditions     List of conditions.
 * @param array $table_fields   List of product fields.
 */
function fn_product_variations_exim_pre_processing_by_product_type(&$pattern, &$conditions, &$table_fields)
{
    $table_fields[] = 'products.product_type AS product_type';
    $table_fields[] = 'products.variation_options AS variation_options';
    $table_fields[] = 'products.parent_product_id AS parent_product_id';
    $table_fields[] = 'IF(products.parent_product_id = 0, products.product_id, products.parent_product_id) AS sort';

    if (!empty($pattern['condition']['product_id'])) {
        $conditions[] = db_quote(
            '(products.product_id IN (?n) OR products.parent_product_id IN (?n))',
            $pattern['condition']['product_id'], $pattern['condition']['product_id']
        );
    }

    $pattern['order_by'] = 'sort, product_id';
}

/**
 * Prepares product data.
 *
 * @param array $data       Raw result of exported products.
 * @param array $result     Formatted result of exported products.
 * @param array $multi_lang List of exported languages.
 * @param array $pattern    Exim schema.
 */
function fn_product_variations_exim_processing_by_product_type($data, &$result, $multi_lang, $pattern)
{
    /** @var ProductManager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];
    $product_type = $product_manager->getProductTypeInstance(ProductManager::PRODUCT_TYPE_VARIATION);
    $export_fields = $pattern['export_fields'];

    foreach ($result as $key => &$items) {
        foreach ($items as $lang_code => &$product) {
            $data_item = $data[$key][$lang_code];

            if ($data_item['product_type'] === ProductManager::PRODUCT_TYPE_VARIATION) {
                foreach ($product as $exim_field => $value) {
                    if (!empty($export_fields['multilang'][$exim_field]['allow_for_variation'])
                        || !empty($export_fields['main'][$exim_field]['allow_for_variation'])
                    ) {
                        continue;
                    }

                    if (isset($export_fields['multilang'][$exim_field]['db_field'])) {
                        $field = $export_fields['multilang'][$exim_field]['db_field'];
                    } elseif (isset($export_fields['main'][$exim_field]['db_field'])) {
                        $field = $export_fields['main'][$exim_field]['db_field'];
                    } else {
                        $field = strtolower($exim_field);
                    }

                    if (!$product_type->isFieldAvailable($field)) {
                        $product[$exim_field] = null;
                    }
                }
            }

            unset($product);
        }
    }

    unset($items);
}

/**
 * Wrapper for generates SEO name for imported product.
 *
 * @param int       $object_id      Product identifier
 * @param string    $object_type    One-letter object type identifier
 * @param string    $object_name    SEO-name to import with
 * @param array     $product_name   Product name for specified language code
 * @param int       $index
 * @param string    $dispatch
 * @param string    $company_id     Company identifier
 * @param string    $lang_code      Two-letter language code
 * @param string    $company_name   Company name product imported for
 * @param array     $row            Import data
 *
 * @return array SEO name for specified language code
 */
function fn_product_variations_create_import_seo_name($object_id, $object_type = 'p', $object_name, $product_name, $index = 0, $dispatch = '', $company_id = '', $lang_code = CART_LANGUAGE, $company_name = '', $row)
{
    if ($object_type == 'p') {
        $product_type = null;

        if (isset($row['product_type'])) {
            $product_type = $row['product_type'];
        } elseif (isset($row['Product type'])) {
            $product_type = $row['Product type'];
        }

        if ($product_type === ProductManager::PRODUCT_TYPE_VARIATION) {
            return array();
        }
    }

    return fn_create_import_seo_name($object_id, $object_type, $object_name, $product_name, $index, $dispatch, $company_id, $lang_code, $company_name);
}

/**
 * Updates product amount for configurable product after import.
 *
 * @param array $primary_object_ids
 *
 * @return bool
 */
function fn_product_variations_exim_prepare_product_amount($import_data)
{
    $product_ids = array();

    foreach ($import_data as $item) {
        $row = reset($item);

        $product_type = fn_product_variations_exim_get_product_type($row);
        $product_id = isset($row['product_id']) ? $row['product_id'] : null;

        if ($product_id && $product_type === ProductManager::PRODUCT_TYPE_CONFIGURABLE) {
            $product_ids[] = $product_id;
        }
    }

    if (!empty($product_ids)) {
        /** @var ProductManager $product_manager */
        $product_manager = Tygh::$app['addons.product_variations.product.manager'];
        $product_manager->actualizeConfigurableProductAmount($product_ids);
    }

    return true;
}

/**
 * Filtrates tracking value for configurable products
 *
 * @param array $row
 */
function fn_product_variations_exim_filter_tracking_value(&$row)
{
    /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    $product_type = fn_product_variations_exim_get_product_type($row);

    if ($product_type === ProductManager::PRODUCT_TYPE_CONFIGURABLE && isset($row['tracking'])) {
        $row['tracking'] = $product_manager->normalizeTracking($row['tracking']);
    }
}

/**
 * Gets product type.
 *
 * @param array $row
 *
 * @return null|string
 */
function fn_product_variations_exim_get_product_type($row)
{
    $product_type = null;

    if (isset($row['product_type'])) {
        $product_type = $row['product_type'];
    } elseif (isset($row['Product type'])) {
        $product_type = $row['Product type'];
    }

    return $product_type;
}

/**
 * Checks and prepares variation attributes (variation_options, variation_code, product_type) before importing product
 *
 * @param array   $primary_object_id Array containing object identifier
 * @param array   $product           Product data
 * @param boolean $skip_record       Skip importing row flag
 * @param array   $processed_data    Array containing quantities of imported products
 */
function fn_product_variations_exim_check_variation_options($primary_object_id, &$product, &$skip_record, &$processed_data)
{
    /** @var ProductManager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    $product_id = !empty($primary_object_id['product_id']) ? $primary_object_id['product_id'] : 0;
    $product_exists = (bool) $product_id;
    $product_type = fn_product_variations_exim_get_product_type($product);
    $result = new OperationResult();

    if (isset($product['variation_options']) && !$skip_record) {

        // if variation options are not empty we the consider product as a variation
        if ($product['variation_options']) {

            if (!empty($product_type) && $product_type !== ProductManager::PRODUCT_TYPE_VARIATION) {
                // configurable product with variation options is invalid
                $result->addError(
                    'conf_product_cannot_have_variation_options',
                    __(
                        'product_variations.exim_error_conf_product_cannot_have_variation_options',
                        array('[product_code]' => $product['product_code'])
                    )
                );

            } else {
                // updating or creating a variation
                $stored_parent_product_data = fn_product_variations_exim_get_stored_parent_product_data();
                $product_type = ProductManager::PRODUCT_TYPE_VARIATION;
                $selected_options = false;

                if (empty($stored_parent_product_data['product_id'])) {
                    // cannot find parent product for variation
                    $result->addError(
                        'cannot_find_parent_for_variation',
                        __(
                            'product_variations.exim_error_cannot_find_parent_for_variation',
                            array('[product_code]' => $product['product_code'])
                        )
                    );

                } else {
                    $parent_product_data = fn_product_variations_exim_get_parent_product_data($stored_parent_product_data['product_id'], $product['lang_code']);

                    $selected_options = fn_product_variations_exim_prepare_variation_options_for_import(
                        $product,
                        $parent_product_data['product_options']
                    );
                }

                if ($selected_options) {
                    $variation_code = $product_manager->getVariationCode($parent_product_data['product_id'], $selected_options);
                    $variation_exists = false;
                    $variation_changed = true;

                    if ($product_exists) {
                        $variation_changed = $product_manager->getProductFieldValue($product_id, 'variation_code') !== $variation_code;
                    }

                    if ($variation_changed
                        && $product_manager->existsProductVariation($parent_product_data['product_id'], $variation_code, true)
                    ) {
                        // variation with these options already exists
                        $variation_exists = true;
                        $result->addError(
                            'variation_with_provided_options_exists',
                            __(
                                'product_variations.exim_error_variation_with_provided_options_exists',
                                array('[product_code]' => $product['product_code'])
                            )
                        );
                    }

                    $is_parent_product_new = !$stored_parent_product_data['object_exists'];

                    if (!$variation_exists) {

                        // if parent product has a wrong type, or it is newly created (therefore it does not have proper options saved)
                        if ($parent_product_data['product_type'] !== ProductManager::PRODUCT_TYPE_CONFIGURABLE
                            || $is_parent_product_new
                            || count(array_diff($parent_product_data['variation_options'], array_keys($selected_options))) > 0 // option ids mismatch
                        ) {
                            $converted = fn_product_variations_exim_convert_parent_product_to_configurable(
                                $parent_product_data,
                                $selected_options
                            );

                            if ($converted) {
                                $processed_type = $is_parent_product_new ? 'N' : 'E';
                                $processed_data['by_types'][$parent_product_data['product_type']][$processed_type]--;
                                $processed_data['by_types'][ProductManager::PRODUCT_TYPE_CONFIGURABLE][$processed_type]++;
                            }
                        }

                        $result->setSuccess(true);
                        $product['variation_options'] = $product_manager->encodeVariationSelectedOptions($selected_options);
                        $product['variation_code'] = $variation_code;
                        $product['product_type'] = ProductManager::PRODUCT_TYPE_VARIATION;
                        $product['parent_product_id'] = $parent_product_data['product_id'];
                    }
                }
            }
        } else {

            if ($product_type === ProductManager::PRODUCT_TYPE_VARIATION) {
                // variation without variation_options is not valid
                $result->addError(
                    'variation_cannot_have_empty_variation_options',
                    __(
                        'product_variations.exim_error_variation_cannot_have_empty_variation_options',
                        array('[product_code]' => $product['product_code'])
                    )
                );

            } else {
                // it might be configurable product, so variation_options must be unset
                unset($product['variation_options']);
                $result->setSuccess(true);

                // set product type for a new product
                if (!$product_exists && !empty($product_type)) {
                    $product['product_type'] = $product_type;
                }
            }
        }
    } elseif (!isset($product['variation_options'])
        && (empty($product_type)
            || $product_type === ProductManager::PRODUCT_TYPE_SIMPLE || $product_type === ProductManager::PRODUCT_TYPE_CONFIGURABLE)
    ) {
        $result->setSuccess(true);
    }

    if (empty($product_type) || empty($processed_data['by_types'][$product_type])) {

        if ($product_exists) {
            $product_type = $product_manager->getProductFieldValue($product_id, 'product_type');
        } else {
            $product_type = ProductManager::PRODUCT_TYPE_SIMPLE;
        }
    }

    if (!$result->isSuccess()) {
        $skip_record = true;
        $processed_data['S']++;
        $processed_data['by_types'][$product_type]['S']++;
        $result->showNotifications();
    } elseif (!$skip_record) {
        if ($product_exists) {
            $processed_data['by_types'][$product_type]['E']++;
        } else {
            $processed_data['by_types'][$product_type]['N']++;
        }
    }
}

/**
 * Sets notification that contains quantities of imported product by their types
 *
 * @param array $processed_data            Array containing quantities of imported products
 * @param array $final_import_notification Notifications array
 */
function fn_product_variations_exim_set_import_final_notification($processed_data, &$final_import_notification)
{
    $updated = $created = $skipped = 0;

    foreach ($processed_data['by_types'] as $quantities) {
        $updated += $quantities['E'];
        $created += $quantities['N'];
        $skipped += $quantities['S'];
    }

    $final_import_notification =  __('product_variations.text_exim_data_imported', array(
        '[new_simple]'     => $processed_data['by_types'][ProductManager::PRODUCT_TYPE_SIMPLE]['N'],
        '[upd_simple]'     => $processed_data['by_types'][ProductManager::PRODUCT_TYPE_SIMPLE]['E'],
        '[skipped_simple]' => $processed_data['by_types'][ProductManager::PRODUCT_TYPE_SIMPLE]['S'],
        '[new_var]'        => $processed_data['by_types'][ProductManager::PRODUCT_TYPE_VARIATION]['N'],
        '[upd_var]'        => $processed_data['by_types'][ProductManager::PRODUCT_TYPE_VARIATION]['E'],
        '[skipped_var]'    => $processed_data['by_types'][ProductManager::PRODUCT_TYPE_VARIATION]['S'],
        '[new_conf]'       => $processed_data['by_types'][ProductManager::PRODUCT_TYPE_CONFIGURABLE]['N'],
        '[upd_conf]'       => $processed_data['by_types'][ProductManager::PRODUCT_TYPE_CONFIGURABLE]['E'],
        '[skipped_conf]'   => $processed_data['by_types'][ProductManager::PRODUCT_TYPE_CONFIGURABLE]['S'],
        '[new]'            => $created,
        '[exist]'          => $updated,
        '[skipped]'        => $skipped,
        '[total]'          => $updated + $created + $skipped,
    ));
}

/**
 * Initializes new counters for imported products by product types
 *
 * @param array $processed_data Array containing quantities of imported products
 */
function fn_product_variations_exim_add_processed_data_fields(&$processed_data)
{
    $initial_values = array(
        'E' => 0, // existent
        'N' => 0, // new
        'S' => 0, // skipped
    );

    $processed_data['by_types'] = array(
        ProductManager::PRODUCT_TYPE_SIMPLE => $initial_values,
        ProductManager::PRODUCT_TYPE_CONFIGURABLE => $initial_values,
        ProductManager::PRODUCT_TYPE_VARIATION => $initial_values,
    );
}

/**
 * Stores parent product data for later use by variations
 *
 * @param array $primary_object_id Object id
 * @param array $product           Product data
 * @param bool  $object_exists     Flag that defines if the product existed before import
 */
function fn_product_variations_exim_store_parent_product_data($primary_object_id, $product, $object_exists)
{
    $parent_product_id = null;
    $product_id = !empty($primary_object_id['product_id']) ? $primary_object_id['product_id'] : null;
    $product_type = fn_product_variations_exim_get_product_type($product);

    if ($product_id === null || $product_type === ProductManager::PRODUCT_TYPE_SIMPLE) {
        // simple product (or product without ID) cannot be parent for a variation
        $parent_product_id = 0;
    } elseif ($product_type !== ProductManager::PRODUCT_TYPE_VARIATION && empty($product['variation_options'])) {
        $parent_product_id = $product_id;
    }

    if ($parent_product_id !== null) {
        Registry::set('runtime.exim.product_variations.parent_product_data', array('product_id' => $parent_product_id, 'object_exists' => $object_exists));
    }
}

/**
 * Fetches parent product data from registry
 *
 * @return mixed|null
 */
function fn_product_variations_exim_get_stored_parent_product_data()
{
    return Registry::get('runtime.exim.product_variations.parent_product_data');
}

/**
 * Fetches additional data for variation's parent product by its id
 *
 * @param int    $product_id Product identifier
 * @param string $lang_code  Two-letter language code
 *
 * @return mixed
 */
function fn_product_variations_exim_get_parent_product_data($product_id, $lang_code)
{
    static $products_cache = array();

    if (!isset($products_cache[$lang_code][$product_id])) {
        /** @var ProductManager $product_manager */
        $product_manager = Tygh::$app['addons.product_variations.product.manager'];
        $parent_product_type = $product_manager->getProductFieldValue($product_id, 'product_type');
        $parent_product_variation_options = $product_manager->getProductVariationOptionsValue($product_id);

        $parent_product_data = array(
            'product_id'        => $product_id,
            'product_type'      => $parent_product_type,
            'variation_options' => $parent_product_variation_options,
        );

        $options = fn_product_variations_get_available_options($product_id, $lang_code);
        $parent_product_data['product_options'] = $options->isSuccess() ? $options->getData() : array();

        $products_cache[$lang_code][$product_id] = $parent_product_data;
    }

    return $products_cache[$lang_code][$product_id];
}

/**
 * Converts product to configurable
 *
 * @param array $product Product data
 * @param array $variation_options Selected variation options
 *
 * @return bool
 */
function fn_product_variations_exim_convert_parent_product_to_configurable($product, $variation_options)
{
    static $changed_cache = array();
    $product_id = !empty($product['product_id']) ? $product['product_id'] : null;
    $changed = false;

    if (!isset($changed_cache[$product_id])) {
        /** @var ProductManager $product_manager */
        $product_manager = Tygh::$app['addons.product_variations.product.manager'];
        $changed_cache[$product_id] = true;
        $option_ids = array_keys($variation_options);
        sort($option_ids);

        $product_manager->changeProductTypeToConfigurable($product_id, $option_ids);
        $changed = true;
    }

    return $changed;
}

/**
 * Core set function wrapper, that allows skip options update process
 *
 * @param array  $product            Product data from importing file
 * @param string $product_code       Product code
 * @param int    $product_id         Product identifier
 * @param array  $data               Product data grouped by language
 * @param string $lang_code          Two-letter language code
 * @param string $features_delimiter Features delimiter
 */
function fn_product_variations_exim_set_product_options($product, $product_code, $product_id, $data, $lang_code, $features_delimiter)
{
    $continue = true;

    if (!empty($data[$lang_code])) {
        $product_type = fn_product_variations_exim_get_product_type($product);
        $is_variation_product = $product_type === ProductManager::PRODUCT_TYPE_VARIATION || !empty($product['variation_options']);

        /** @var ProductManager $product_manager */
        $product_manager = Tygh::$app['addons.product_variations.product.manager'];

        if ($is_variation_product
            || $product_manager->hasProductVariations($product_id)
        ) {
            // do not update options for variation or product that has variations
            $continue = false;

            fn_set_notification('W', __('warning'), __('product_variations.exim_error_cannot_update_product_options_for_variation_or_parent', array(
                '[product_code]' => $product_code,
            )));
        }
    }

    if ($continue) {
        fn_exim_set_product_options($product_id, $data, $lang_code, $features_delimiter);
    }
}

/**
 * Converts product variation options to a string for export
 *
 * @param array  $product   Product data
 * @param string $lang_code Two-letter language code
 *
 * @return string
 */
function fn_product_variations_exim_get_variation_options($product, $lang_code)
{
    /** @var array $product_options_cache Internal cache */
    static $product_options_cache = array();

    if ($product['product_type'] != ProductManager::PRODUCT_TYPE_VARIATION || empty($product['parent_product_id'])) {
        return '';
    }

    /** @var ProductManager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];
    $variation_options = $product_manager->getProductVariationOptions($product);

    if (empty($variation_options)) {
        return '';
    }

    $parent_product_id = $product['parent_product_id'];

    if (!isset($product_options_cache[$lang_code][$parent_product_id])) {
        $product_options_cache[$lang_code][$parent_product_id] = fn_get_product_options($parent_product_id, $lang_code);
    }

    $product_options = $product_options_cache[$lang_code][$parent_product_id];
    $prepared_options = array();

    foreach ($variation_options as $option_id => $variant_id) {
        $option = isset($product_options[$option_id]) ? $product_options[$option_id] : array();
        $variant = isset($product_options[$option_id]['variants'][$variant_id]) ? $product_options[$option_id]['variants'][$variant_id] : array();

        if ($option && $variant) {
            $prepared_options[$option_id] = implode(PRODUCT_VARIATIONS_EXIM_OPTION_AND_VARIANT_DELIMITER, array(
                fn_exim_wrap_value($option['option_name'], PRODUCT_VARIATIONS_EXIM_OPTION_AND_VARIANT_ENCLOSURE, PRODUCT_VARIATIONS_EXIM_OPTION_AND_VARIANT_DELIMITER),
                fn_exim_wrap_value($variant['variant_name'], PRODUCT_VARIATIONS_EXIM_OPTION_AND_VARIANT_ENCLOSURE, PRODUCT_VARIATIONS_EXIM_OPTION_AND_VARIANT_DELIMITER),
            ));
        } else {
            $prepared_options = array();

            fn_set_notification('E', __('error'), __('product_variations.variation_option_is_not_available_for_parent', array(
                '[option_id]' => $option_id,
                '[product_code]' => $product['product_code'],
            )));
            break;
        }
    }

    return implode(
        PRODUCT_VARIATIONS_EXIM_OPTIONS_DELIMITER,
        fn_exim_wrap_value($prepared_options, PRODUCT_VARIATIONS_EXIM_OPTIONS_ENCLOSURE, PRODUCT_VARIATIONS_EXIM_OPTIONS_DELIMITER)
    );
}

/**
 * Parses product variation options string back to array for import
 *
 * @param array $product         Product data
 * @param array $product_options Product options
 *
 * @return array|bool
 */
function fn_product_variations_exim_prepare_variation_options_for_import($product, $product_options)
{
    $prepared_options = array();

    if (!empty($product['variation_options']) && !empty($product_options)) {
        $failed = false;
        $variation_options = str_getcsv(
            $product['variation_options'],
            PRODUCT_VARIATIONS_EXIM_OPTIONS_DELIMITER,
            PRODUCT_VARIATIONS_EXIM_OPTIONS_ENCLOSURE
        );

        foreach ($variation_options as $variation_option) {
            $option_id = $variant_id = 0;
            list($option_name, $variant_name) = str_getcsv(
                $variation_option,
                PRODUCT_VARIATIONS_EXIM_OPTION_AND_VARIANT_DELIMITER,
                PRODUCT_VARIATIONS_EXIM_OPTION_AND_VARIANT_ENCLOSURE
            );

            if (empty($option_name) || empty($variant_name)) {
                fn_set_notification('E', __('error'), __('product_variations.exim_error_invalid_variation_options_format', array(
                    '[product_code]' => $product['product_code'],
                )));
                $failed = true;
                break;
            }

            foreach ($product_options as $option) {

                if ($option['option_name'] == $option_name) {
                    $option_id = $option['option_id'];
                    break;
                }
            }

            if (empty($option_id)) {
                fn_set_notification('E', __('error'), __('product_variations.exim_error_option_is_not_available', array(
                    '[option_name]' => $option_name,
                    '[product_code]' => $product['product_code'],
                )));
                $failed = true;
                break;
            }

            foreach ($product_options[$option_id]['variants'] as $variant) {

                if ($variant['variant_name'] == $variant_name) {
                    $variant_id = $variant['variant_id'];
                    break;
                }
            }

            if (empty($variant_id)) {
                // TODO: consider implementing new variant creation logic
                fn_set_notification('E', __('error'), __('product_variations.exim_error_variant_does_not_exist', array(
                    '[option_name]' => $option_name,
                    '[variant_name]' => $variant_name,
                    '[product_code]' => $product['product_code'],
                )));
                $failed = true;
                break;
            }

            $prepared_options[$option_id] = $variant_id;
        }

    } else {
        fn_set_notification('E', __('error'), __('product_variations.exim_error_invalid_variation_options_provided', array(
            '[product_code]' => $product['product_code'],
        )));
        $failed = true;
    }

    return $failed ? false : $prepared_options;
}

function fn_product_variations_exim_default_variation($object_id, &$row)
{
    /** @var ProductManager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    if (empty($row['product_type']) || $row['product_type'] == ProductManager::PRODUCT_TYPE_CONFIGURABLE) {
        return;
    }

    if (empty($row['parent_product_id'])) {
        return;
    }

    if (empty($row['is_default_variation'])) {
        $variation_options = $product_manager->getDefaultVariationOptions($row['parent_product_id']);

        if (!$variation_options) {
            $row['is_default_variation'] = 'Y';
        }

    } else {
        $variation_options = $product_manager->getDefaultVariationOptions($row['parent_product_id']);
        $is_default_variation = empty($row['is_default_variation']) ? 'N' : $row['is_default_variation'];
        unset($row['is_default_variation']);

        if (!$variation_options) {
            $row['is_default_variation'] = 'Y';

        } elseif ($is_default_variation == 'Y') {
            $product_id = empty($object_id['product_id']) ? 0 : $object_id['product_id'];
            $product_manager->updateDefaultVariation($product_id, $row['parent_product_id']);

            $row['is_default_variation'] = 'Y';
        }
    }
}