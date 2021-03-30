<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
	'calculate_cart_items',
    'pre_get_cart_product_data',
    'pre_update_order',
    'calculate_cart_post',
    'place_suborders_pre',
    'place_suborders',
    'get_product_fields'
);
