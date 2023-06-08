<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    //'place_order_post',
    'allow_place_order_post',
    'get_promotions_search_by_query',
    'get_logos_post',
    'before_dispatch',
    'api_exec',
    'update_product_post'
);
