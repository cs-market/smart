<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'pre_get_cart_product_data',
    'get_product_data',
    'load_products_extra_data'
);
