<?php

if (!fn_allowed_for('ULTIMATE:FREE')) {
    $schema['conditions']['total_conditioned_products'] = array(
        'operators' => array ('gte'),
        'type' => 'input',
        'field_function' => array('fn_category_promotion_check_total_conditioned_products', '#id', '@cart_products'),
        'zones' => array('cart'),
    );
}

return $schema;
