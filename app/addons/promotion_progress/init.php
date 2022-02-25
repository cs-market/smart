<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'get_promotions',
    'pre_promotion_validate',
    'get_user_info',
    'update_user_profile_pre'
);
