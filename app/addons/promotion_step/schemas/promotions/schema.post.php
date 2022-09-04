<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

use Tygh\Registry;

$schema['conditions']['promotion_step'] = array(
    'operators' => array ('gte'),
    'type' => 'input',
    'field_function' => array('fn_promotion_step_get_products_amount', '#id', '@cart', '@cart_products', 'C'),
    'zones' => array('cart'),
    'filter' => 'fn_promotions_filter_float_condition_value'
);

if (Registry::get('addons.product_packages.status') == 'A') {
    $schema['conditions']['promotion_package_step'] = array(
        'operators' => array ('gte'),
        'type' => 'input',
        'field_function' => array('fn_promotion_step_get_products_amount', '#id', '@cart', '@cart_products', 'C', 1),
        'zones' => array('cart'),
        'filter' => 'fn_promotions_filter_float_condition_value'
    );
}
$schema['conditions']['promotion_step_limit'] = array(
    'operators' => array ('eq'),
    'type' => 'input',
    'field_function' => array('fn_promotion_step_unconditional_true'),
    'zones' => array('cart')
);

$schema['bonuses']['promotion_step_free_products'] = array(
    'type' => 'picker',
    'picker_props' => array (
        'picker' => 'pickers/products/picker.tpl',
        'params' => array (
            'type' => 'table',
            'aoc' => true
        ),
    ),
    'function' => array('fn_promotion_step_apply_cart_rule','#this', '@cart', '@auth', '@cart_products'),
    'zones' => array('cart'),
);

$schema['bonuses']['promotion_step_give_condition_products'] = array(
    'type' => 'input',
    'function' => array('fn_promotion_step_apply_cart_rule','#this', '@cart', '@auth', '@cart_products'),
    'zones' => array('cart'),
);

return $schema;
