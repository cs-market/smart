<?php

use Tygh\Registry;
use Tygh\Enum\YesNo;
use Tygh\Enum\SiteArea;
use Tygh\Api\Response;

defined('BOOTSTRAP') or die('Access denied');

function fn_settings_variants_addons_order_cancellation_cancellation_status() {
    return fn_get_simple_statuses('O');
}

function fn_order_cancellation_get_orders_post($params, &$orders) {
    if ((SiteArea::isStorefront(AREA) || defined('API')) && !empty($orders)) {
        $statuses = array_column($orders, 'status');
        $statuses_data = fn_get_statuses(STATUSES_ORDER, $statuses);
        if (fn_allowed_for('MULTIVENDOR')) {
            $companies_data = db_get_hash_single_array('SELECT company_id, allow_order_cancellation FROM ?:companies WHERE company_id IN (?a)', ['company_id', 'allow_order_cancellation'], array_unique(array_column($orders, 'company_id')));
        }

        foreach ($orders as &$order) {
            $order['allow_cancel'] = (fn_allowed_for('ULTIMATE') || (!empty($order['company_id']) && YesNo::toBool($companies_data[$order['company_id']]))) ? $statuses_data[$order['status']]['params']['allow_cancel'] : YesNo::NO;

            fn_set_hook('order_cancellation_extra_check', $order['allow_cancel'], $order);
        }
        unset($order);
    }
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
    $order['allow_cancel'] = YesNo::NO;
    if (!empty($order) && (fn_allowed_for('ULTIMATE') || (!empty($order['company_id']) && YesNo::toBool(db_get_field('SELECT allow_order_cancellation FROM ?:companies WHERE company_id = ?i', $order['company_id']))))) {
        $status_data = fn_get_status_params($order['status'], STATUSES_ORDER);
        if (!empty($status_data) && YesNo::toBool($status_data['allow_cancel'])) {
            $order['allow_cancel'] = YesNo::YES;
        }

        fn_set_hook('order_cancellation_extra_check', $order['allow_cancel'], $order);
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
