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
        'process_put' => array('fn_exim_set_delivery_date', '#this', '#key', $key),
        'linked' => false, // this field is not linked during import-export
    ];
}

$schema['export_fields']['iney delivery days'] = [
    'db_field' => 'delivery_date',
    'process_get' => array('fn_exim_get_delivery_date_line', '#this'),
    'pre_insert' => array('fn_exim_set_delivery_date_line', '#this'),
];

return $schema;
