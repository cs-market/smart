<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'promotion_apply_pre',
    'get_promotions',
    'get_product_data_post',
    'get_products_before_select',
    'calculate_cart_post'
);
