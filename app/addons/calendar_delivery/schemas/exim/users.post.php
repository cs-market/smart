<?php

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'calendar_delivery/schemas/exim/users.functions.php');

$days = [
    '1' => __("weekday_exim_1"),
    '2' => __("weekday_exim_2"),
    '3' => __("weekday_exim_3"),
    '4' => __("weekday_exim_4"),
    '5' => __("weekday_exim_5"),
    '6' => __("weekday_exim_6"),
    '0' => __("weekday_exim_0")
];

foreach ($days as $key => $day) {
    $key = (string) $key;

    $field_name = __("calendar_delivery.exim_user_delivery_date", ['%day%' => $day]);

    $schema['export_fields'][$field_name] = [
        // not change it!
        // 'db_field' => 'delivery_date',
        'process_get' => array('fn_exim_get_delivery_date', '#key', $key),
        'db_field' => 'delivery_date_'.$key,
        'linked' => false, // this field is not linked during import-export
    ];
}

$schema['import_process_data']['import_delivery_days'] = array(
    'function' => 'fn_exim_set_delivery_date',
    'args' => array('$primary_object_id', '$object'),
    'import_only' => true,
);

$schema['export_fields']['Delivery days'] = [
    'db_field' => 'delivery_date',
];

if (Registry::get('addons.storages.status') == 'A') {
    $schema['export_fields']['Ignore exception days'] = [
        'db_field' => 'ignore_exception_days',
    ];
}

return $schema;
