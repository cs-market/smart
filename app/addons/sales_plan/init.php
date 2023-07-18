<?php

defined('BOOTSTRAP') or die('Access denied');

fn_register_hooks(
    'post_delete_user',
    'delete_company',
    'create_order',
    'place_order',
    'get_users',
    'get_user_info'
);
