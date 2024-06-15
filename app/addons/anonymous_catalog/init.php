<?php

defined('BOOTSTRAP') or die('Access denied');

fn_register_hooks(
    'get_products',
    ['get_product_data', 4294967295]
);
