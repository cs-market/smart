<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
	'calculate_cart_taxes_pre',
    'pre_get_cart_product_data',
    'gather_additional_products_data_params',
    'pre_update_order'
);
