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
            'period' => 'C',
            'time_to' => strtotime('-2 minutes'),
            'time_from' => strtotime('-30 minutes'),
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
        $params['time_to'] = strtotime('-30 minutes');
        $params['time_from'] = strtotime('-7 days');
        $mailer = Tygh::$app['mailer'];
        foreach ($allowed_companies as $params['company_id']) {
            list($orders) = fn_get_orders($params);
            if (!empty($orders)) {
                foreach ($orders as $order) {
                    $mailer->send(array(
                        'to' => ['novikova_t@baltika.com', 'fedorova_oo@carlsbergee.com', 'lysenko_kn@baltika.com', 'usenko_ls@baltika.com', 'porotova_mv@baltika.com'],
                        'from' => 'default_company_orders_department',
                        'data' => array('data' => $data),
                        'subject' => 'Smart distribution: Не удалось отправить заказ #' . $order['order_id'],
                        'body' => "Внимание! Не удалось отправить заказ #" . $order['order_id'],
                    ), 'A');
                    fn_change_order_status($order['order_id'], 'E');
                }
            }
        }
    }
    exit();
} elseif ($mode == 'send_order') {
    if (empty($action)) fn_print_die('missing order_id');
    $xml = fn_monolith_generate_xml($action);
    if (fn_monolith_send_xml($xml)) {
        fn_change_order_status($action, 'A');
        fn_print_die('success', $action);
    }
    fn_print_die('failure', $action);
}
