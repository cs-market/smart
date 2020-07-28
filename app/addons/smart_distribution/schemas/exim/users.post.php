<?php

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'smart_distribution/schemas/exim/users.smart_distribution.functions.php');

$schema['pre_processing']['add_field_columns'] =    array(
    'function' => 'fn_exim_smart_distribution_add_field_columns_import',
    'args' => array('$import_data', '$pattern'),
    'import_only' => true,
);

$schema['pre_processing']['rejoin_user_profiles'] =    array(
    'function' => 'fn_exim_rejoin_user_profiles_export',
    'args' => array('$pattern'),
    'import_only' => true,
);

$schema['import_process_data']['unset_product_id'] = array(
    'function' => 'fn_exim_smart_distribution_check_profile_id',
    'args' => array('$primary_object_id', '$object', '$pattern'),
    'import_only' => true,
);

$schema['export_fields']['Managers'] = [
    'process_put' => array('fn_exim_smart_distribution_add_vendors_customers', '#key', '#this'),
    'process_get' => array ('fn_exim_smart_distribution_export_vendors_customers', '#key'),
    'import_only' => false,
    'linked' => false,
];

$schema['export_fields']['Profile id'] = [
    'db_field' => 'profile_id',
    'table' => 'user_profiles',
];

$schema['references']['user_data'] = array(
    'reference_fields' => array('user_id' => '#key', 'type' => POINTS),
    'join_type' => 'LEFT'
);

$schema['export_fields']['Reward points'] = [
    'process_get' => array('unserialize', '#this'),
    'export_only' => true,
    'db_field' => 'data',
    'table' => 'user_data',
];

$schema['export_fields']['Add user group IDs'] = [
    'process_put' => array('fn_exim_set_add_usergroups', '#key', '#this'),
    'import_only' => true,
    'linked' => false,
];

return $schema;
