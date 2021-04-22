<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'update_product_pre',
    'update_product_post',
    'gather_additional_product_data_before_discounts',
    'gather_additional_product_data_post',
    'pre_add_to_cart',
    'add_product_to_cart_get_price',
    'get_product_price_post',
    'check_amount_in_stock_before_check',
    'update_cart_products_pre',
    'post_check_amount_in_stock',
    'get_product_price_pre',
    'update_product_amount_post',
    'dispatch_assign_template',
    'set_admin_notification'
);