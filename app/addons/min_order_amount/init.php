<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'get_user_info',
    'calculate_cart_post',
    'allow_place_order_post',
    'get_usergroups',
    'pre_place_order'
);
