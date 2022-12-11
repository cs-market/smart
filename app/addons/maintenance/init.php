<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'pre_add_to_cart',
    'update_storage_usergroups_pre',
    'update_product_prices',
    ['update_product_pre', 1000],
    'update_profile',
    'get_promotions',
    'dispatch_assign_template',
);
