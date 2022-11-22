<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

include_once(Registry::get('config.dir.addons') . 'user_price/schemas/exim/user_price.functions.php');

$schema = array(
    'section' => 'products',
    'pattern_id' => 'user_price',
    'name' => __('user_price'),
    'key' => array('product_id'),
    'order' => 1,
    'table' => 'products',
    'import_skip_db_processing' => true,
    'import_only' => true,
    'permissions' => array(
        'import' => 'manage_catalog',
        'export' => 'view_catalog',
    ),
    'references' => array(
        'products' => array(
            'reference_fields' => array('product_id' => '#key'),
            'join_type' => 'INNER',
        ),
    ),
    'options' => array(
        'price_dec_sign_delimiter' => array(
            'title' => 'price_dec_sign_delimiter',
            'description' => 'text_price_dec_sign_delimiter',
            'type' => 'input',
            'default_value' => '.'
        ),
    ),
    'export_fields' => array(
        'Product code' => array(
            'db_field' => 'product_code',
            'alt_key' => true,
            'required' => true,
        ),
        'Price' => array(
            'db_field' => 'price',
            'required' => false,
            'convert_put' => array('fn_exim_import_price', '#this', '@price_dec_sign_delimiter'),
        ),
        'Name' => array(
            'linked' => false,
            'required' => true,
        ),
    ),
    'import_process_data' => array(
        'import_user_price' => array(
            'function' => 'fn_import_user_price',
            'args' => array('$primary_object_id', '$object', '$options', '$processed_data', '$processing_groups'),
            'import_only' => true,
        ),
    ),
);

$schema['import_get_primary_object_id'] = array(
    'fill_primary_object_company_id' => array(
        'function' => 'fn_exim_apply_company',
        'args' => array('$pattern', '$alt_keys', '$object', '$skip_get_primary_object_id'),
        'import_only' => true,
    ),
);

// if (Registry::get('runtime.company_id')) {
//     $schema['references']['products']['reference_fields'] = array('product_id' => '#key', 'company_id' => Registry::get('runtime.company_id'));
// }

return $schema;
