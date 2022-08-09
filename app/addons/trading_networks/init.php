<?php

defined('BOOTSTRAP') or die('Access denied');

fn_register_hooks(
    'get_users',
    'fill_auth',
    'user_logout_after',
    'get_storages',
    'user_roles_get_list',
    'smart_auth_auth_routines'
);

fn_init_stack(array('fn_init_network', &$_REQUEST));
