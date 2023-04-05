<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'get_product_data',
    'load_products_extra_data',
    'update_product_post',
    'pre_get_cart_product_data',
    'get_cart_product_data',
    'calculate_cart_taxes_pre',
    'check_add_to_cart_post',
    'reward_points_calculate_item',
);
