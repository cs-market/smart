<?php

defined('BOOTSTRAP') or die('Access denied');

fn_register_hooks(
    'get_usergroups',
    'get_simple_usergroups',
    'update_usergroup',
    'delete_usergroups',
    'get_default_usergroups',
    'update_product_pre',
    'update_category_pre',
    'update_category_post',
    'vendor_plan_before_save',
    'category_promotion_get_products_before_select',
);
