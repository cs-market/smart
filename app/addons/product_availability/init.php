<?php

defined('BOOTSTRAP') or die('Access denied');

fn_register_hooks(
    'update_product_pre',
    'get_products',
    'get_product_data'
);
