<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

define('USER_EXTRA_DATA', 'E');

fn_register_hooks(
    'get_user_info',
    'user_init'
);
