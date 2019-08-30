<?php

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'smart_distribution/schemas/exim/users.smart_distribution.functions.php');

$schema['pre_processing']['add_field_columns'] =  array(
  'function' => 'fn_exim_smart_distribution_add_field_columns_import',
  'args' => array('$import_data', '$pattern'),
  'import_only' => true,
);

$schema['export_fields']['Managers'] = [
  'process_put' => array('fn_exim_smart_distribution_add_vendors_customers', '#key', '#this'),
  'process_get' => array ('fn_exim_smart_distribution_export_vendors_customers', '#key'),
  'import_only' => false,
  'linked' => false,
];

return $schema;
