<?php

use Tygh\Tools\DateTimeHelper;

$schema['conditions']['progress_total_paid'] = array (
    'operators' => array ('gte', 'gt'),
    'type' => 'input',
    'field_function' => array('fn_promotion_validate_promotion_progress', '#id', '#this', '@auth'),
    'zones' => array('cart')
);
$schema['conditions']['progress_order_amount'] = array (
    'operators' => array ('gte', 'gt'),
    'type' => 'input',
    'field_function' => array('fn_promotion_validate_promotion_progress', '#id', '#this', '@auth'),
    'zones' => array('cart')
);
$schema['conditions']['progress_average_paid'] = array (
    'operators' => array ('gte', 'gt'),
    'type' => 'input',
    'field_function' => array('fn_promotion_validate_promotion_progress', '#id', '#this', '@auth'),
    'zones' => array('cart')
);
$schema['conditions']['progress_purchased_unique_sku'] = array (
    'operators' => array ('gte', 'gt'),
    'type' => 'input',
    'field_function' => array('fn_promotion_validate_promotion_progress', '#id', '#this', '@auth'),
    'zones' => array('cart')
);
$schema['conditions']['progress_purchased_total_amount'] = array (
    'operators' => array ('gte', 'gt'),
    'type' => 'input',
    'field_function' => array('fn_promotion_validate_promotion_progress', '#id', '#this', '@auth'),
    'zones' => array('cart')
);
$schema['conditions']['progress_period'] = array (
    'operators' => array ('in'),
    'type' => 'select',
    'variants' => [DateTimeHelper::PERIOD_THIS_MONTH => 'this_month', DateTimeHelper::PERIOD_LAST_MONTH => 'previous_month', DateTimeHelper::PERIOD_MONTH_AGO_TILL_NOW => 'last_30_days', DateTimeHelper::PERIOD_THIS_WEEK => 'this_week', DateTimeHelper::PERIOD_LAST_WEEK => 'previous_week', DateTimeHelper::PERIOD_WEEK_AGO_TILL_NOW => 'last_7_days', DateTimeHelper::PERIOD_TODAY => 'today'],
    'zones' => array('cart')
);
return $schema;
