<?php

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'managers/schemas/exim/orders.functions.php');

$schema['export_fields']['Managers'] = array(
    'db_field' => 'user_id',
    'export_only' => true,
    'process_get' => array('fn_exim_get_managers_names', '#this')
);

return $schema;
