<?php

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'smart_distribution/schemas/exim/users.smart_distribution.functions.php');

//  change default
$schema['pre_processing']['rejoin_user_profiles'] =  array(
  'function' => 'fn_exim_rejoin_user_profiles_export',
  'args' => array('$pattern'),
  'import_only' => true,
);

$schema['pre_processing']['add_field_columns'] =  array(
  'function' => 'fn_exim_smart_distribution_add_field_columns_import',
  'args' => array('$import_data', '$pattern'),
  'import_only' => true,
);

//  create profile, if empty profile_id
$schema['import_process_data']['unset_product_id'] = array(
    'function' => 'fn_exim_smart_distribution_check_profile_id',
    'args' => array('$primary_object_id', '$object'),
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

  return $schema;
