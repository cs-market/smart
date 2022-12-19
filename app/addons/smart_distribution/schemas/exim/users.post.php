<?php

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'smart_distribution/schemas/exim/users.functions.php');

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

$schema['import_process_data']['check_profile_id'] = array(
    'function' => 'fn_exim_smart_distribution_check_profile_id',
    'args' => array('$primary_object_id', '$object', '$pattern'),
    'import_only' => true,
);

$schema['export_fields']['Profile id'] = [
    'db_field' => 'profile_id',
    'table' => 'user_profiles',
];

$schema['export_fields']['Profile name'] = [
    'db_field' => 'profile_name',
    'table' => 'user_profiles',
];

$schema['import_process_data']['get_salts'] = array(
    'function' => 'fn_exim_get_salts',
    'args' => array('$primary_object_id', '$object', '$pattern', '$import_data'),
    'import_only' => true,
);

$schema['import_process_data']['set_default_pass_for_baltika'] = array(
    'function' => 'fn_exim_smart_distribution_set_default_pass_for_baltika',
    'args' => array('$primary_object_id', '$object'),
    'import_only' => true,
);

if (Registry::get('addons.calendar_delivery.status') == 'A') {
    $schema['export_fields']['iney delivery days'] = [
        'db_field' => 'delivery_date',
        'process_get' => array('fn_exim_get_delivery_date_line', '#this'),
        'pre_insert' => array('fn_exim_set_delivery_date_line', '#this'),
    ];
}

return $schema;
