<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'call_requests_do_call_request_post',
    'add_discussion_post_post',
    'get_order_info',
    'change_order_status',
    'get_user_info',
    'get_users',
    'place_order'
);