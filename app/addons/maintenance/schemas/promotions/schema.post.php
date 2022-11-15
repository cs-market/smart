<?php

$schema['conditions']['catalog_once_per_customer'] = array(
    'type' => 'statement',
    'field_function' => array('fn_maintenance_promotion_get_dynamic', '#id', '#this', 'catalog_once_per_customer', '@cart', '@auth'),
    'zones' => array('catalog')
);

return $schema;
