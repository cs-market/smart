<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'auth_routines',
    'user_exist'
);
