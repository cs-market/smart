<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if ($mode == 'update_details') {
		if (!empty($_REQUEST['update_order_history'])) {
			foreach ($_REQUEST['update_order_history'] as $log_id => $message) {
				$allowed = true;
				if ($auth['is_root'] != 'Y') {
					$allowed = db_get_field("SELECT action FROM ?:order_history WHERE log_id = ?i AND user_id = ?i", $log_id, $auth['user_id']);
				}
				if (empty($allowed)) {
					fn_set_notification('W', __('warning'), __('text_ips_denied'));
				} else {
					db_query("UPDATE ?:order_history SET message = ?s WHERE log_id = ?i", $message, $log_id);
				}
			}
		}
	}

	return;
}

if ($mode == 'details') {
	if (fn_ecl_check_order_history_privilege() != false) {
		$tabs = Registry::get('navigation.tabs');

		$tabs['order_history'] = array(
			'title' => __('order_history'),
			'js' => true
		);
		Registry::set('navigation.tabs', $tabs);
		
		$params = array(
			'order_id' => $_REQUEST['order_id'],
			'page' => !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1
		);
		list($order_history, $search) = fn_get_order_history($params, Registry::get('settings.Appearance.admin_elements_per_page'));
		Registry::get('view')->assign('order_history', $order_history);
		Registry::get('view')->assign('order_history_search', $search);
	}
}