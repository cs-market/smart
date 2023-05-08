<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }
//fn_print_die(Tygh::$app['session']['auth']);
fn_register_hooks(
    'update_product_post',
    ['gather_additional_product_data_before_discounts',4294967295],
    'pre_get_cart_product_data',
    'get_cart_product_data',
    'calculate_cart_taxes_pre',
    'add_product_to_cart_get_price',
    'pre_place_order',
    'get_order_info',
    'user_init',
    'change_order_status'
);
