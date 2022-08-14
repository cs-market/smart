<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'get_orders',
    'get_order_info',
    'exim1c_order_xml_pre',
    'get_companies',
    'pre_update_order',
    'place_order',
    'form_cart_pre_fill',
    'update_cart_by_data_post',
    'form_cart',
    'update_user_pre',
    'update_user_profile_pre',
    'fill_auth',
    'get_user_info',
    'get_user_short_info_pre',
    'user_init',
    'calculate_cart_content_after_shipping_calculation',
    'get_usergroups',
    'allow_place_order_post',
    'update_storage_pre',
    'get_storages',
    'delete_storages',
    'post_delete_user',
    'min_order_amount_extra_additional_ordering'
);

// backward compatibility
fn_register_hooks('get_company_data');

define('DOCUMENT_ORIGINALS', 'D');
