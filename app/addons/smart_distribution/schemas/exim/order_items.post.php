<?php

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'smart_distribution/schemas/exim/order_items.functions.php');

$schema['key'] = array('product_code', 'order_id');

unset($schema['export_fields']['Item ID']['alt_key'], $schema['export_fields']['Item ID']['required']);

$schema['export_fields']['Product code']['required'] = true;
$schema['export_fields']['Product code']['alt_key'] = true;

$schema['import_process_data']['fill_product_id'] = [
    'function' => 'fn_import_order_items_fill_product_id',
    'args' => array('$primary_object_id', '$object', '$pattern', '$options', '$processed_data', '$processing_groups', '$skip_record'),
    'import_only' => true,
];

return $schema;
