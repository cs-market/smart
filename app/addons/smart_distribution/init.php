<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'get_orders',
    'get_order_info',
    'vendor_plan_before_save',
    'get_usergroups',
    'post_get_usergroups',
    'get_simple_usergroups_pre',
    'get_users_pre',
    'get_users',
    'get_users_post',
    'get_user_info_before',
    'get_user_info',
    'update_user_pre',
    'update_user_profile_pre',
    'update_profile',
    'gather_additional_product_data_post',
    'sales_reports_table_condition',
    'sales_reports_change_table',
    'get_default_usergroups',
    'update_category_pre',
    'update_category_post',
    'set_product_categories_exist',
    'pre_add_to_cart',
    'add_to_cart',
    'get_profile_fields',
    'vendor_communication_add_thread_message_post',
    'pre_update_order',
    //'get_company_id_by_name',
    'update_product_pre',
    'get_product_features_list_before_select',
    'shippings_get_shippings_list_conditions',
    'place_order',
    'update_order',
    'get_notification_rules',
    'get_product_features_list_post',
    'form_cart',
    'calculate_cart_post',
    'update_cart_by_data_post',
    'get_products',
    'get_products_before_select',
    'get_categories',
    'get_product_data',
    'get_product_data_post',
    'get_product_price_post',
    'load_products_extra_data',
    'load_products_extra_data_post',
    'get_stickers_pre',
    'get_product_features',
    'send_form',
    'mailer_send_post',
    'update_product_feature_pre',
    'update_product_feature_post',
    'get_product_filters_before_select',

    'calculate_cart_items',
    'edit_place_order',
    'promotion_apply_pre',
    'add_product_to_cart_get_price',
    'pre_get_cart_product_data',
    'checkout_get_user_profiles',
    'get_mailboxes_pre',
    'get_tickets_params',
    'update_ticket_pre',
    'usergroup_types_get_map_user_type',
    //temporary
    'get_orders_post',
    'extract_cart',
    'get_promotions',
    'text_cart_amount_corrected_notification',
    'get_orders_totals',
    'sberbank_edit_item'
);
