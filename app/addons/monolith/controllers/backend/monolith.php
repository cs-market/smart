<?php

use Tygh\Registry;
use Tygh\Http;


if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return array(CONTROLLER_STATUS_OK);
}

if ($mode == 'cron') {
    $addon = Registry::get('addons.monolith');
    $allowed_companies = explode(',', $addon['company_ids']);
    if (!empty($allowed_companies)) {
        $params = array(
            'status' => ['O', 'P'],
            'total_to' => strtotime('-15 minutes'),
        );
        foreach ($allowed_companies as $params['company_id']) {
            list($orders) = fn_get_orders($params);
            if (!empty($orders)) {
                foreach ($orders as $order) {
                    $xml = fn_monolith_generate_xml($order['order_id']);
                    if (fn_monolith_send_xml($xml)) {
                        fn_change_order_status($order['order_id'], 'A');
                    }
                }
            }
        }
    }
    exit();
}
