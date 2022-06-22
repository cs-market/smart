<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'pre_add_to_cart',
    'check_add_to_cart_post',
    'add_product_to_cart_get_price',
    'add_to_cart',
    'get_cart_product_data',
    'post_add_to_cart',
    'save_cart_content_pre',
    'pre_place_order',
    'get_orders_post',
    'get_order_info',
    'change_order_status',
    'calculate_cart_post',
    'load_products_extra_data',
    'get_products_post',
    ['gather_additional_product_data_before_discounts', 4394967294],
    'reward_points_calculate_item'
);
