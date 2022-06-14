<?php

use Tygh\Tools\DateTimeHelper;

$schema['conditions']['budget'] = array (
    'operators' => array ('eq'),
    'type' => 'input',
    'filter' => 'fn_promotions_filter_float_condition_value',
    'field_function' => array('fn_promotion_validate_budget', '#id', '#this', '@cart'),
    'zones' => array('cart')
);
return $schema;
