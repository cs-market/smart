<?php

defined('BOOTSTRAP') or die('Access denied');

fn_register_hooks(
    'get_usergroups_pre',
    'update_product_post',
    ['get_product_data', 27000000],
    ['load_products_extra_data_post', 27000000],
    ['get_products', 27000000],
    'load_products_extra_data',
    'pre_add_to_cart',
    'add_product_to_cart_get_price',
    'pre_get_cart_product_data',
    'get_cart_product_data',
    'generate_cart_id',
    ['check_amount_in_stock_before_check', 100],
    'shippings_group_products_list',
    'pre_update_order',
    'update_product_amount_pre',
    'update_product_amount'
);

fn_init_stack(array('fn_init_storages', &$_REQUEST));
