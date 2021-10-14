<?php

$schema['conditions']['progress_total_paid'] = array (
    'operators' => array ('lte', 'gte', 'lt', 'gt'),
    'type' => 'input',
    'field_function' => array('fn_promotion_validate_promotion_progress', '#id', '#this', '@auth'),
    'zones' => array('catalog', 'cart')
);
$schema['conditions']['progress_order_amount'] = array (
    'operators' => array ('lte', 'gte', 'lt', 'gt'),
    'type' => 'input',
    'field_function' => array('fn_promotion_validate_promotion_progress', '#id', '#this', '@auth'),
    'zones' => array('catalog', 'cart')
);
$schema['conditions']['progress_purchased_products'] = array (
    'operators' => array ('in'),
    'type' => 'picker',
    'picker_props' => array (
        'picker' => 'pickers/products/picker.tpl',
        'params' => array (
            'type' => 'table',
            'display' => ''
        ),
    ),
    // 'field_function' => array('fn_promotion_validate_purchased_product', '#this', '@product', '@auth'),
    'zones' => array('catalog', 'cart')
    /*'zones' => array(
        'catalog',
    )*/
);
$schema['conditions']['progress_period'] = array (
    'operators' => array ('in'),
    'type' => 'select',
    'variants' => ['this_month' => 'this_month', 'previous_month' => 'previous_month', 'last_30_days' => 'last_30_days', 'this_week' => 'this_week', 'today' => 'today'],
    'zones' => array('catalog', 'cart')
    /*'zones' => array(
        'catalog',
    )*/
);
return $schema;
