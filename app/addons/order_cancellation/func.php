<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_settings_variants_addons_order_cancellation_cancellation_status() {
	return fn_get_simple_statuses('O');
}

function fn_order_cancellation_get_status_params_definition(&$status_params, &$type) {
	if ($type == STATUSES_ORDER) {
		$status_params['allow_cancel'] = array (
				'type' => 'checkbox',
				'label' => 'allow_order_cancellation'
		);
	} 

	return true;
}

function fn_order_cancellation_get_order_info(&$order, $additional_data) {
	if (!empty($order)) {
		$status_data = fn_get_status_params($order['status'], STATUSES_ORDER);
		if (!empty($status_data) && (!empty($status_data['allow_cancel']) && $status_data['allow_cancel'] == 'Y')) {
			$order['allow_cancel'] = 'Y';
		}
	}
}