<?php

if (!fn_allowed_for('ULTIMATE:FREE')) {
    $schema['conditions']['total_conditioned_products'] = array(
        'operators' => array ('gte'),
        'type' => 'input',
        'field_function' => array('fn_category_promotion_check_total_conditioned_products', '#id', '#this', '@cart_products'),
        'zones' => array('cart'),
    );
}

if (!fn_allowed_for('ULTIMATE:FREE')) {
    $schema['conditions']['amount_conditioned_products'] = array(
        'operators' => array ('eq', 'neq', 'lte', 'gte', 'lt', 'gt'),
        'type' => 'input',
        'field_function' => array('fn_category_promotion_check_amount_conditioned_products', '#id', '#this', '@cart_products'),
        'zones' => array('cart'),
    );
}
if (!fn_allowed_for('ULTIMATE:FREE')) {
    $schema['conditions']['unique_amount_conditioned_products'] = array(
        'operators' => array ('eq', 'neq', 'lte', 'gte', 'lt', 'gt'),
        'type' => 'input',
        'field_function' => array('fn_category_promotion_check_unique_amount_conditioned_products', '#id', '#this', '@cart_products'),
        'zones' => array('cart'),
    );
}

return $schema;
