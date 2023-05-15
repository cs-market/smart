<?php

use Tygh\Registry;

if (!fn_allowed_for('ULTIMATE:FREE')) {
    $schema['conditions']['total_conditioned_products'] = array(
        'operators' => array ('gte'),
        'type' => 'input',
        'field_function' => array('fn_category_promotion_check_total_conditioned_products', '#id', '#this', '@cart_products'),
        'zones' => array('cart'),
    );

    $schema['conditions']['amount_conditioned_products'] = array(
        'operators' => array ('eq', 'neq', 'lte', 'gte', 'lt', 'gt'),
        'type' => 'input',
        'field_function' => array('fn_category_promotion_check_amount_conditioned_products', '#id', '#this', '@cart_products'),
        'zones' => array('cart'),
    );

    $schema['conditions']['unique_amount_conditioned_products'] = array(
        'operators' => array ('eq', 'neq', 'lte', 'gte', 'lt', 'gt'),
        'type' => 'input',
        'field_function' => array('fn_category_promotion_check_unique_amount_conditioned_products', '#id', '#this', '@cart_products'),
        'zones' => array('cart'),
    );

    if (Registry::get('addons.product_packages.status') == 'A') {
        $schema['conditions']['limit_discount_bonus_by_amount_packages'] = array(
            'operators' => array ('eq'),
            'type' => 'input',
            'field_function' => array(function() {return true;}),
            'zones' => array('cart'),
        );
    }

    $schema['bonuses']['discount_on_products_from_conditions'] = array(
        'type' => 'picker',
        'picker_props' => array (
            'picker' => 'pickers/products/picker.tpl',
            'params' => array (
                'type' => 'links',
            ),
        ),
        'function' => array('fn_category_promotion_apply_cart_rule', '#this', '@cart', '@auth', '@cart_products'),
        'discount_bonuses' => array('to_percentage', 'by_percentage', 'to_fixed', 'by_fixed'),
        'zones' => array('cart'),
        'filter' => 'floatval',
        'filter_field' => 'discount_value'
    );

    $schema['bonuses']['static_discount_on_products_from_conditions'] = array(
        'function' => array('fn_category_promotion_apply_cart_rule', '#this', '@cart', '@auth', '@cart_products'),
        'discount_bonuses' => array('to_percentage', 'by_percentage', 'to_fixed', 'by_fixed'),
        'zones' => array('cart'),
        'filter' => 'floatval',
        'filter_field' => 'discount_value'
    );
}

return $schema;
