<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'calculate_cart_items',
    'pre_get_cart_product_data',
    'get_cart_product_data_post_options',
    'calculate_cart_post',
    'pre_update_order',
    'place_suborders_pre',
    'place_suborders',
    'get_product_fields',
    'pre_get_orders',
    'get_products',
);
