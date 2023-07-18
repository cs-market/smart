<?php

defined('BOOTSTRAP') or die('Access denied');

fn_register_hooks(
    'get_products_before_select',
    'get_products',
    'update_promotion_post',
    'get_autostickers_pre',
    'gather_additional_products_data_post',
    'get_promotions_pre',
    'get_promotions',
    'get_promotions_post',
    'pre_add_to_cart',
    'generate_cart_id',
    'promotion_apply_pre'
);
