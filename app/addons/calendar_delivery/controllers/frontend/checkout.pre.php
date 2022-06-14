<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$cart = &Tygh::$app['session']['cart'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_REQUEST['delivery_date']) && is_array($_REQUEST['delivery_date'])) {
        //fn_print_die($_REQUEST, $cart);
        $cart['delivery_date'] = $_REQUEST['delivery_date'];
        // foreach($_REQUEST['delivery_date'] as $group_id => $delivery_date) {
        //     $cart['product_groups'][$group_id]['delivery_date'] = $delivery_date;
        // }

        // foreach ($_REQUEST['delivery_period'] as $company_id => $period) {
        //     $choosed_ts = fn_parse_date($delivery_date[$company_id]);

        //     // if !today
        //     if (date('d', $choosed_ts) != date('d')) {
        //         continue;
        //     }

        //     // if start period hour < now
        //     if (strstr($period, ':', true) < date('h')) {
        //         $res = false;
        //         $c_data = fn_get_company_data($company_id);

        //         if (count($cart['product_groups']) > 1)
        //             fn_set_notification('N', __('notice'), __('calendar_delivery.choose_another_period_vendor') . ' ' . $c_data['company']);
        //         else {
        //             fn_set_notification('N', __('notice'), __('calendar_delivery.choose_another_period'));
        //         }
        //     }
        // }
        // $cart['delivery_period'] = isset($_REQUEST['delivery_period']) ? $_REQUEST['delivery_period'] : '';

        // if (!$res) return [CONTROLLER_STATUS_REDIRECT, 'checkout.checkout'];
    }
    if (!empty($_REQUEST['documents_originals']) && is_array($_REQUEST['documents_originals'])) {
        $cart['documents_originals'] = $_REQUEST['documents_originals'];
    }
}
