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
	'get_user_info_before',
	'gather_additional_product_data_post',
	'sales_reports_table_condition',
	'sales_reports_change_table',
	'get_default_usergroups',
	'api_handle_request',
	'api_send_response',
	'update_category_pre',
	'update_category_post',
	'set_product_categories_exist',
	'pre_add_to_cart',
	'get_profile_fields',
	'vendor_communication_add_thread_message_post',
	'pre_update_order',
	'get_company_id_by_name',
	'update_product_pre',
	'get_product_features_list_before_select'
);