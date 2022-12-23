<?php

use Tygh\Registry;
use Tygh\Enum\YesNo;
use Tygh\Api\Response;

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
    if (!empty($order) && (fn_allowed_for('ULTIMATE') || (!empty($order['company_id']) && YesNo::toBool(db_get_field('SELECT allow_order_cancellation FROM ?:companies WHERE company_id = ?i', $order['company_id']))))) {
        $status_data = fn_get_status_params($order['status'], STATUSES_ORDER);
        if (!empty($status_data) && YesNo::toBool($status_data['allow_cancel'])) {
            $order['allow_cancel'] = YesNo::YES;
        }
    }
}

function fn_order_cancellation_api_delete_order($id, &$status, $data) {
    $order = fn_get_order_info($id);
    if (!empty($order)) {
        $status_data = fn_get_status_params($order['status'], STATUSES_ORDER);
        if (!empty($status_data) && YesNo::toBool($status_data['allow_cancel'])) {
            fn_change_order_status($order['order_id'], Registry::get('addons.order_cancellation.cancellation_status'));
            $status = Response::STATUS_NO_CONTENT;
        }
    }
}
