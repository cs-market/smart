<?php

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'smart_distribution/schemas/exim/orders.functions.php');

$schema['export_fields']['1c'] = array(
    'linked' => false,
    'process_get' => array('fn_get_1c_code', '#key')
);

$schema['export_fields']['Payment method'] = array(
    'db_field' => 'payment_id',
    'process_get' => array('fn_get_payment_name', '#this', '#lang_code')
);

$schema['export_fields']['E-mail']['required'] = false;

$schema['import_process_data']['change_order_status'] = array(
    'function' => 'fn_import_change_order_status',
    'args' => array('$object'),
    'import_only' => true,
);

$schema['export_fields']['Date']['process_get'][0] = 'fn_timestamp_to_date_wo_time';

$schema['export_fields']['Total initial'] = array(
    'linked' => false,
    'process_get' => array('fn_get_total_history', 'asc', '#key')
);

$schema['export_fields']['Total final'] = array(
    'linked' => false,
    'process_get' => array('fn_get_total_history', 'desc' , '#key')
);

return $schema;
