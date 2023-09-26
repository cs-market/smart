<?php

defined('BOOTSTRAP') or die('Access denied');

use Tygh\Addons\Telegram\ServiceProvider;

Tygh::$app->register(new ServiceProvider());

fn_register_hooks(
    'api_get_user_data_pre',
    'fill_auth',
    'api_handle_request',
    'api_check_access',
    'place_order_post',
    'get_users'
);
