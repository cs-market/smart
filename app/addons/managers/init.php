<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'get_user_info',
    'user_roles_get_list',
    'get_orders',
    'get_order_info',
    'get_users',
    'update_user_profile_pre',
    'vendor_communication_add_thread_message_post',
    'place_order',
    'user_init',
    'generate_sales_report',
    'generate_sales_report_post',
    'delete_user',
    'get_tickets_params',
    'send_form',
    'update_ticket_pre',
    'sales_reports_dynamic_conditions'
);
