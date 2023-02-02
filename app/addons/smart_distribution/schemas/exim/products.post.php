<?php

use Tygh\Registry;

include_once(Registry::get('config.dir.schemas') . 'exim/products.functions.php');
include_once(Registry::get('config.dir.addons') . 'smart_distribution/schemas/exim/products.functions.php');

$schema['export_fields']['Detailed image']['process_put'] = ['fn_exim_smart_distribution_import_images', '@images_path', '%Thumbnail%', '#this', '0', 'M', '#key', 'product'];

$schema['export_fields']['Category']['process_put'][0] = 'fn_exim_sd_set_product_categories';
// $schema['export_fields']['Category']['default'] = '!Потерянные товары';
$schema['export_fields']['Secondary categories']['process_put'][0] = 'fn_exim_sd_set_product_categories';
$schema['pre_processing']['prepare_default_categories']['function'] = 'fn_sd_import_prepare_default_categories';
$schema['pre_processing']['prepare_default_categories']['args'][] = '$import_data';

if (fn_allowed_for('MULTIVENDOR') && (!Registry::get('runtime.company_id'))) {
	$schema['export_fields']['Category']['process_put'][] = '%Vendor%';
	$schema['export_fields']['Secondary categories']['process_put'][] = '%Vendor%';
}

$schema['export_fields']['Features']['process_put'] = ['fn_exim_smart_distribution_set_product_features', '#key', '#this', '@features_delimiter', '#lang_code', '%Vendor%' ];
$schema['import_process_data']['fill_vendor_ugroups_if_empty'] = array(
    'function' => 'fn_fill_vendor_ugroups_if_empty', 
    'args' => array('$primary_object_id', '$object', '$pattern', '$options', '$processed_data', '$processing_groups', '$skip_record'),
    'import_only' => true,
);

if (fn_allowed_for('MULTIVENDOR')) {
    $schema['import_process_data']['fill_tracking_for_new_products'] = array(
        'function' => 'fn_fill_tracking_for_new_products', 
        'args' => array('$primary_object_id', '$object'),
        'import_only' => true,
    );
}

return $schema;
