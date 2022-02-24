<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'get_orders',
    'calculate_cart_taxes_pre',
    'get_order_info',
    'exim1c_order_xml_pre',
    'get_companies',
    'create_order',
    'update_order',
    'place_order',
    'form_cart_pre_fill',
    'update_cart_by_data_post',
    'form_cart',
    'update_user_pre',
    'get_user_info',
    'calculate_cart_content_after_shipping_calculation',
    'get_usergroups'
);

// backward compatibility
fn_register_hooks('get_company_data');
