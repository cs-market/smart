<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$cart = &Tygh::$app['session']['cart'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_REQUEST['delivery_date']) && is_array($_REQUEST['delivery_date'])) {
        $delivery_date = $_REQUEST['delivery_date'];
        foreach ($delivery_date as $company_id => $date) {
            $res = true;
            $choosed_ts = fn_parse_date($date);

            $c_data = fn_get_company_data($company_id);
            $compare_ts = fn_ts_this_day(fn_calendar_get_nearest_delivery_day($c_data, true));

            if ($choosed_ts < $compare_ts) {
                $res = false;
            }
            if ($c_data['saturday_shipping'] == 'N' && date('w', $choosed_ts) == 6) {
                $res = false;
            }
            if ($c_data['sunday_shipping'] == 'N' && date('w', $choosed_ts) == 0) {
                $res = false;
            }
            if ($c_data['monday_rule'] == 'N' && date('w', $choosed_ts) == 1 && ((date('w', time()) == 0) || (date('w', time()) == 6 && date('H', time()) >= 16 ))) {
                $res = false;
            }

            $shipping = reset($cart['shipping']);
            if (isset($shipping['service_params']['limit_weekday'])) {
                if ($shipping['service_params']['limit_weekday'] != '' && $shipping['service_params']['limit_weekday'] != 'C') {
                    if (date('w', $choosed_ts) != $shipping['service_params']['limit_weekday']) {
                        $res = false;
                    }
                } elseif ($shipping['service_params']['limit_weekday'] == 'C') {
                    if (!in_array(date('w', $choosed_ts), $shipping['service_params']['customer_shipping_calendar'])) {
                        $res = false;
                    }
                }
            }

            if (!$res) {
                if (count($cart['product_groups']) > 1)
                    fn_set_notification('N', __('notice'), __('calendar_delivery.choose_another_day_vendor') . ' ' . $c_data['company']);
                else {
                    fn_set_notification('N', __('notice'), __('calendar_delivery.choose_another_day'));
                }
                $_REQUEST['next_step'] = 'ster_three';
            }
        }

        $cart['delivery_date'] = $_REQUEST['delivery_date'];

        foreach ($_REQUEST['delivery_period'] as $company_id => $period) {
            $choosed_ts = fn_parse_date($delivery_date[$company_id]);

            // if !today
            if (date('d', $choosed_ts) != date('d')) {
                continue;
            }

            // if start period hour < now
            if (strstr($period, ':', true) < date('h')) {
                $c_data = fn_get_company_data($company_id);

                if (count($cart['product_groups']) > 1)
                    fn_set_notification('N', __('notice'), __('calendar_delivery.choose_another_period_vendor') . ' ' . $c_data['company']);
                else {
                    fn_set_notification('N', __('notice'), __('calendar_delivery.choose_another_period'));
                }
                $_REQUEST['next_step'] = 'ster_three';
            }
        }

    }

    $cart['delivery_period'] = isset($_REQUEST['delivery_period']) ? $_REQUEST['delivery_period'] : '';
}
