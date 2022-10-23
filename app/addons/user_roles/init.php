<?php

defined('BOOTSTRAP') or die('Access denied');

fn_register_hooks(
    'get_users_pre',
    'get_users',
    'fill_auth',
    'user_init',
    'get_user_short_info_pre'
);
