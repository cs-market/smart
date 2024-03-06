<?php

defined('BOOTSTRAP') or die('Access denied');

fn_register_hooks(
    'get_orders_post',
    'get_status_params_definition',
    'get_order_info',
    'api_delete_order'
);
