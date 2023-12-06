<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'create_order',
    'user_logout_before_clear_cart'
);
