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

unset($schema['post_processing']['unset_static_orders']);

foreach ($schema['export_fields'] as &$field) {
    if (isset($field['process_get'][0]) && $field['process_get'][0] == 'fn_exim_orders_with_items_get') {
        unset($field['process_get']);
    }
}
$schema['export_fields']['Date']['process_get'] = array('fn_timestamp_to_date', '#this');

$schema['export_fields']['Total initial'] = array(
    'linked' => false,
    'process_get' => array('fn_get_total_history', 'asc', '#key')
);

$schema['export_fields']['Total final'] = array(
    'linked' => false,
    'process_get' => array('fn_get_total_history', 'desc' , '#key')
);

return $schema;
