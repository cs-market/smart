<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if ($mode == 'cancel') {
		$params = $_REQUEST;
		if (isset($params['order_id'])) {
			$order = fn_get_order_info($params['order_id']);
			if (!empty($order)) {
				$status_data = fn_get_status_params($order['status'], STATUSES_ORDER);
				if (!empty($status_data) && (!empty($status_data['allow_cancel']) && $status_data['allow_cancel'] == 'Y')) {
					fn_change_order_status($order['order_id'], Registry::get('addons.order_cancellation.cancellation_status'));
					fn_redirect(fn_url('orders.details&order_id='.$order['order_id']));
				}
			}
		}
	} 
	return array(CONTROLLER_STATUS_OK);
}
