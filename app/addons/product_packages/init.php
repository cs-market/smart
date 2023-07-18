<?php

defined('BOOTSTRAP') or die('Access denied');

fn_register_hooks(
    'pre_get_cart_product_data',
    'get_product_data',
    'load_products_extra_data',
    'pre_add_to_cart',
    'exim_1c_import_value_fields',
    'exim_1c_import_features_definition',
    'exim_1c_import_features_values'
);
