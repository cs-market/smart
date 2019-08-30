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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * @var array $schema
 */

include_once __DIR__ . '/products.functions.php';

$processing_schemas = array('pre_export_process', 'export_pre_moderation', 'export_processing', 'import_process_data', 'post_processing');

foreach ($processing_schemas as $schema_name) {
    if (!isset($schema[$schema_name])) {
        $schema[$schema_name] = array();
    }
}

// Fields to export
$schema['export_fields']['Product type'] = array(
    'db_field' => 'product_type',
);

$schema['export_fields']['Variation options'] = array(
    'process_get' => array('fn_product_variations_exim_get_variation_options', '#row', '#lang_code'),
    'db_field' => 'variation_options',
);

$schema['export_fields']['Is default product variation'] = array(
    'db_field' => 'is_default_variation',
);

$schema['export_fields']['Options']['process_put'] = array('fn_product_variations_exim_set_product_options', '#row', '%Product code%', '#key', '#this', '#lang_code', '@features_delimiter');

// Export pre-moderation
$schema['export_pre_moderation']['pre_moderation_by_product_type'] = array(
    'function' => 'fn_product_variations_exim_pre_moderation_by_product_type',
    'args' => array('$pattern'),
);

// Export processing
$schema['export_processing']['processing_by_product_type'] = array(
    'function' => 'fn_product_variations_exim_processing_by_product_type',
    'args' => array('$data', '$result', '$multi_lang', '$pattern'),
);

// Pre processing
$schema['pre_processing']['add_processed_data_fields'] = array(
    'function' => 'fn_product_variations_exim_add_processed_data_fields',
    'args' => array('$processed_data'),
    'import_only' => true,
);

// Export pre-processing
$schema['pre_export_process']['pre_processing_by_product_type'] = array(
    'function' => 'fn_product_variations_exim_pre_processing_by_product_type',
    'args' => array('$pattern', '$conditions', '$table_fields'),
);

// Import processing
$schema['import_process_data']['variations_filter_tracking_value'] = array(
    'function' => 'fn_product_variations_exim_filter_tracking_value',
    'args' => array('$object'),
    'import_only' => true,
);

$schema['import_process_data']['product_variations_check_variation_options'] = array(
    'function' => 'fn_product_variations_exim_check_variation_options',
    'args' => array('$primary_object_id', '$object', '$skip_record', '$processed_data'),
    'import_only' => true
);

$schema['import_process_data']['product_variations_default_variation'] = array(
    'function' => 'fn_product_variations_exim_default_variation',
    'args' => array('$primary_object_id', '$object'),
    'import_only' => true,
);

// Import after-processing
$schema['import_after_process_data']['product_variations_save_parent_product'] = array(
    'function' => 'fn_product_variations_exim_store_parent_product_data',
    'args' => array('$primary_object_id', '$object', '$object_exists'),
    'import_only' => true
);

if (isset($schema['export_fields']['SEO name'])) {
    $schema['export_fields']['SEO name']['process_put'] = array(
        'fn_product_variations_create_import_seo_name', '#key', 'p', '#this', '%Product name%', 0, '', '', '#lang_code', '%Store%', '#row'
    );
}

// Post processing
$schema['post_processing']['prepare_product_amount'] = array(
    'function' => 'fn_product_variations_exim_prepare_product_amount',
    'args' => array('$import_data'),
    'import_only' => true,
);

$schema['post_processing']['show_imported_products_by_type_notification'] = array(
    'function' => 'fn_product_variations_exim_set_import_final_notification',
    'args' => array('$processed_data', '$final_import_notification'),
    'import_only' => true,
);

$schema['export_fields']['Items in box']['allow_for_variation'] = true;
$schema['export_fields']['Box size']['allow_for_variation'] = true;

return $schema;