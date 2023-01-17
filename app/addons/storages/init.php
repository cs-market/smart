<?php

defined('BOOTSTRAP') or die('Access denied');

fn_register_hooks(
    'update_product_post',
    ['get_product_data', 27000000],
    'get_product_data_post',
    ['get_products', 27000000],
    ['load_products_extra_data', 27000000],
    'load_products_extra_data_post',
    'login_user_post',
    'user_logout_before_save_cart',
    'pre_add_to_cart',
    'add_product_to_cart_get_price',
    'pre_get_cart_product_data',
    'get_cart_product_data',
    'generate_cart_id',
    ['check_amount_in_stock_before_check', 100],
    'calculate_cart_content_before_shipping_calculation',
    'shippings_group_products_list',
    'pre_update_order',
    'update_product_amount_pre',
    'update_product_amount',
    'get_orders',
    'get_order_info',
    ['calculate_cart_items', 100],
    'reorder_product',
    'monolith_generate_xml',
    'get_user_price',
    'user_price_exim_product_price_pre',
    'delete_usergroups',
    'api_runtime_handle_index_request',
    'api_runtime_handle_create_request',
    'api_runtime_handle_delete_request'
);

fn_init_stack(array('fn_init_storages', &$_REQUEST));
