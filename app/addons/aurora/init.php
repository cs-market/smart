<?php

defined('BOOTSTRAP') or die('Access denied');

fn_register_hooks(
    'get_product_data_post',
    'get_products_post'
);
