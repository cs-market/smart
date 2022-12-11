<?php

use Tygh\Registry;

$schema['export_fields']['Delivery date'] = array(
    'db_field' => 'delivery_date',
    'process_get' => array('fn_date_format', '#this', '#'.Registry::get('settings.Appearance.date_format')),
);

$schema['export_fields']['Delivery period'] = array(
    'db_field' => 'delivery_period',
);

return $schema;
