<?php

defined('BOOTSTRAP') or die('Access denied');

fn_register_hooks(
    'pre_add_to_cart',
    'update_storage_usergroups_pre',
    'update_product_prices',
    ['update_product_pre', 1000],
    'update_profile',
    'get_promotions',
    'dispatch_assign_template',
    'check_permission_manage_profiles',
    ['check_rights_delete_user', 1],
    'get_users',
    'mailer_create_message_before',
    'get_payments_pre',
    'shippings_get_shippings_list_conditions',
    'get_user_short_info_pre',
    'save_log',
    'pre_get_orders',
    'development_show_stub',
    'get_carts',
    'change_usergroup_status_pre',
    'exim1c_update_product_usergroups_pre',
    'get_product_filter_fields',
    'get_products',
    'user_init',
    'create_order'
);
