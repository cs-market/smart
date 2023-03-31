<?php

use Tygh\Registry;

$schema = array(
    'section' => 'products',
    'name' => __('earn_points'),
    'pattern_id' => 'product_earn_points',
    'key' => array('product_id'),
    'table' => 'products',
    'permissions' => array(
        'import' => 'manage_catalog',
        'export' => 'view_catalog',
    ),
    'references' => array(
        'reward_points' => array(
            'reference_fields' => array('object_id' => '#key', 'object_type' => 'P'),
            'join_type' => 'LEFT',
        ),
    ),
    'update_only' => true,
    'options' => array(
        'remove_reward_points' => array(
            'title' => 'exim_cleanup_reward_points',
            'type' => 'checkbox',
            'import_only' => true
        ),
    ),
    'import_get_primary_object_id' => array(
        'fill_primary_object_company_id' => array(
            'function' => 'fn_exim_apply_company',
            'args' => array('$pattern', '$alt_keys', '$object', '$skip_get_primary_object_id'),
            'import_only' => true,
        ),
    ),
    'export_fields' => array(
        'Product code' => array(
            'required' => true,
            'alt_key' => true,
            'db_field' => 'product_code'
        ),
        'Usergroup IDs' => array(
            'db_field' => 'usergroup_id',
            'table' => 'reward_points',
            'required' => true,
            'key_component' => true,
            'convert_put' => array('fn_exim_put_usergroup', '#this', '#lang_code'),
        ),
        'Amount' => array(
            'db_field' => 'amount',
            'table' => 'reward_points',
            'required' => true
        ),
        'Vendor' => array(
            'db_field' => 'company_id',
            'required' => true,
        ),
    ),
);

if (fn_allowed_for('MULTIVENDOR')) {
    if (Registry::get('runtime.company_id')) {
        unset($schema['export_fields']['Vendor']);
    }
}

return $schema;
