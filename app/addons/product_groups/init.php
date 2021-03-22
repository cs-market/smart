<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
	'calculate_cart_taxes_pre',
    'pre_get_cart_product_data',
    'gather_additional_products_data_params',
    'pre_update_order',
    'check_min_amount',
    'calculate_cart_post',
    'place_suborders_pre',
    'place_suborders',
    'get_product_fields'
);
