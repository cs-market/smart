<?php

use Tygh\Registry;
use Tygh\Models\Company;
use Tygh\Enum\ProductTracking;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_min_order_amount_get_companies($params, &$fields, $sortings, $condition, $join, $auth, $lang_code, $group) {
	$fields[] = 'min_order_amount';
}

function fn_min_order_amount_get_usergroups($params, $lang_code, &$field_list, $join, $condition, $group_by, $order_by, $limit) {
	$field_list .= ', a.min_order_amount';
}

function fn_min_order_amount_get_user_info($user_id, $get_profile, $profile_id, &$user_data) {
	if (!$user_data['min_order_amount'] && AREA == 'C') {
		$usergroups = array_filter($user_data['usergroups'], function($v) {
			return $v['status'] == 'A';
		});
		if (!empty($usergroups)) {
			$user_data['min_order_amount'] = db_get_field('SELECT max(min_order_amount) FROM ?:usergroups WHERE usergroup_id IN (?a)', array_keys($usergroups));
		}
	}
}