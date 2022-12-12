<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'get_products_before_select',
    'get_products',
    'update_promotion_post',
    'get_autostickers_pre',
    'get_promotions_pre',
    'get_promotions',
    'get_promotions_post'
);
