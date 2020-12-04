<?php

use Tygh\Languages\Languages;
use Tygh\Languages\Values;
use Tygh\Registry;

if ( !defined('AREA') ) { die('Access denied'); }

function fn_calendar_delivery_install()
{
    $service = array(
        'status' => 'A',
        'module' => 'calendar_delivery',
        'code' => 'calendar',
        'sp_file' => '',
        'description' => 'Calendar',
    );

    $service['service_id'] = db_get_field('SELECT service_id FROM ?:shipping_services WHERE module = ?s AND code = ?s', $service['module'], $service['code']);

    if (empty($service['service_id'])) {
        $service['service_id'] = db_query('INSERT INTO ?:shipping_services ?e', $service);
    }

    $languages = Languages::getAll();
    foreach ($languages as $lang_code => $lang_data) {
        $service['lang_code'] = $lang_code;
        db_query('INSERT INTO ?:shipping_service_descriptions ?e', $service);
    }
}

function fn_calendar_delivery_uninstall()
{
    $service_ids = db_get_fields('SELECT service_id FROM ?:shipping_services WHERE module = ?s', 'calendar_delivery');
    if (!empty($service_ids)) {
        db_query('DELETE FROM ?:shipping_services WHERE service_id IN (?a)', $service_ids);
        db_query('DELETE FROM ?:shipping_service_descriptions WHERE service_id IN (?a)', $service_ids);
    }
}

function fn_calendar_delivery_get_order_info(&$order, $additional_data) {
    // backward compatibility
    if (empty($order['delivery_date']) && !empty($order['shipping'][0]['delivery_date'])) {
        $order['delivery_date'] = $order['shipping'][0]['delivery_date'];
    }
    if (empty($order['delivery_period']) && !empty($order['shipping'][0]['delivery_period'])) {
        $order['delivery_period'] = $order['shipping'][0]['delivery_period'];
    }
}

function fn_calendar_delivery_exim1c_order_xml_pre(&$order_xml, $order_data, $cml) {
    if (isset($order_data['delivery_date']) && !empty($order_data['delivery_date'])) {
        $order_xml[$cml['value_fields']][][$cml['value_field']] = array(
            $cml['name'] => $cml['delivery_date'],
            $cml['value'] => fn_date_format($order_data['delivery_date'], Registry::get('settings.Appearance.date_format'))
        );
    }
    if (isset($order_data['delivery_period']) && !empty($order_data['delivery_period'])) {
        $order_xml[$cml['value_fields']][][$cml['value_field']] = array(
            $cml['name'] => $cml['delivery_period'],
            $cml['value'] => $order_data['delivery_period']
        );
    }
}

function fn_calendar_delivery_get_companies($params, &$fields, $sortings, $condition, $join, $auth, $lang_code, $group) {
    //$fields[] = 'tomorrow_rule';
    //$fields[] = 'tomorrow_timeslot';
    $fields[] = 'nearest_delivery';
    $fields[] = 'working_time_till';
    $fields[] = 'saturday_shipping';
    $fields[] = 'sunday_shipping';
    $fields[] = 'monday_rule';
    // backward compatibility remove in June 2020
    $fields[] = "'Y' as after17rule";
    // extra backward compatibility remove in October 2020
    $fields[] = "'Y' as tomorrow_rule";
    $fields[] = 'working_time_till as tomorrow_timeslot';
    $fields[] = 'monday_rule as saturday_rule';

    $fields[] = 'period_start';
    $fields[] = 'period_finish';
    $fields[] = 'period_step';
}

// backward compatibility
function fn_calendar_delivery_get_company_data($company_id, $lang_code, $extra, &$fields, $join, $condition) {
    // remove in October 2020
    $fields[] = "'Y' as tomorrow_rule";
    $fields[] = 'working_time_till as tomorrow_timeslot';
    $fields[] = 'monday_rule as saturday_rule';
    // remove in June 2020
    $fields[] = "'Y' as after17rule";
}

// backward compatibility remove in October 2020
function fn_validate_tomorrow_rule($company_data) {
    $res = false;

    if (is_numeric($company_data)) {
        $company_data = fn_get_company_data($company_data);
    }
    if ($company_data['tomorrow_rule'] == 'Y') {
        $res = (strtotime(date("G:i")) >= strtotime($company_data['tomorrow_timeslot'])) ? true : false;
    }

    return $res;
}

function fn_calendar_get_nearest_delivery_day($company_data, $get_ts = false) {
    $res = false;

    if (is_numeric($company_data)) {
        $company_data = fn_get_company_data($company_data);
    }
    $nearest_delivery = $company_data['nearest_delivery'];
    if (!empty($company_data['working_time_till']) && strtotime(date("G:i")) >= strtotime($company_data['working_time_till'])) {
        $nearest_delivery += 1;
    }
    if ($get_ts) {
        $ts = ($nearest_delivery) ? strtotime("+$nearest_delivery days") : time();
        return $ts;
    } else {
        return $nearest_delivery;
    }
}

if (!is_callable('fn_ts_this_day')) {
    function fn_ts_this_day($timestamp){
        $calendar_format = "d/m/Y";
        if (Registry::get('settings.Appearance.calendar_date_format') == 'month_first') {
            $calendar_format = "m/d/Y";
        }
        $ts = fn_parse_date(date($calendar_format, $timestamp));
        return $ts;
    }
}

function fn_calendar_delivery_create_order(&$order) {
    if ($order['company_id'] && isset( $_SESSION['cart']['delivery_date'][$order['company_id']] )) {
        $order['delivery_date'] = $_SESSION['cart']['delivery_date'][$order['company_id']];
        $order['delivery_period'] = $_SESSION['cart']['delivery_period'][$order['company_id']];
    }
    // backward compatibility with mobile app
    if (is_array($order['delivery_date'])) {
        $order['delivery_date'] = array_shift($order['delivery_date']);
        $order['delivery_period'] = array_shift($order['delivery_period']);
    }
    if (!empty($order['delivery_date'])) {
        $order['delivery_date'] = fn_parse_date($order['delivery_date']);
    }
}

function fn_calendar_delivery_update_order(&$order, $order_id) {
    fn_calendar_delivery_create_order($order);
}

function fn_calendar_delivery_place_order($order_id, $action, $order_status, $cart, $auth) {
    $order = fn_get_order_info($order_id);

    if (empty($order['delivery_date']) && $order['company_id'] == '16' ) {
        $mailer = Tygh::$app['mailer'];

        $mailer->send(array(
            'to' => array('support@i-sd.ru', 'is@i-sd.ru'),
            'from' => 'default_company_orders_department',
            'data' => array('data' => $data),
            'subject' => 'i-sd.ru: Empty delivery date',
            'body' => "Внимание! Размещен заказ #" . $order_id . "без даты доставки",
        ), 'A');
    }
}

function fn_calendar_delivery_update_cart_by_data_post(&$cart, $new_cart_data, $auth) {
    if (isset($new_cart_data['delivery_date'])) $cart['delivery_date'] = $new_cart_data['delivery_date'];
    if (isset($new_cart_data['delivery_period'])) $cart['delivery_period'] = $new_cart_data['delivery_period'];

}

function fn_calendar_delivery_form_cart_pre_fill($order_id, &$cart, $auth, $order_info) {
    if (isset($order_info['delivery_date']))     $cart['delivery_date'] = $order_info['delivery_date'];
    if (isset($order_info['delivery_period']))     $cart['delivery_period'] = $order_info['delivery_period'];
}

function fn_calendar_delivery_form_cart($order_info, &$cart, $auth) {
    if (!empty($order_info['delivery_date'])) {
        $cart['delivery_date'] = $order_info['delivery_date'];
    }
    if (!empty($order_info['delivery_period'])) {
        $cart['delivery_period'] = $order_info['delivery_period'];
    }
}

function fn_calendar_delivery_update_user_pre($user_id, &$user_data, $auth, $ship_to_another, $notify_user) {
    $user_data['delivery_date'] = fn_delivery_date_to_line($user_data['delivery_date']);
}

function fn_calendar_delivery_get_user_info($user_id, $get_profile, $profile_id, &$user_data) {
    $user_data['delivery_date'] = fn_delivery_date_from_line($user_data['delivery_date']);
}

/// TEMP DEPRECATED! Удалить в середине 2020 года
function fn_parse_date_check_year($timestamp, $end_time = false)
{
    if (!empty($timestamp)) {
        if (is_numeric($timestamp)) {
            return $timestamp;
        }

        $ts = explode('/', $timestamp);
        $ts = array_map('intval', $ts);
        if (empty($ts[2])) {
            $ts[2] = date('Y');
        }
        if (count($ts) == 3) {
            list($h, $m, $s) = $end_time ? array(23, 59, 59) : array(0, 0, 0);
            if (Registry::get('settings.Appearance.calendar_date_format') == 'month_first' || $month_first) {
                $timestamp = mktime($h, $m, $s, $ts[0], $ts[1], $ts[2]);
            } else {
                $timestamp = mktime($h, $m, $s, $ts[1], $ts[0], $ts[2]);
            }
            if (date('Y', $timestamp) != '2019') {
                $tmp = $timestamp;
                $timestamp = fn_parse_date($ts[1].'/'.$ts[0].'/'.$ts[2], $end_time);

            }
        } else {
            $timestamp = TIME;
        }
    }

    return !empty($timestamp) ? $timestamp : TIME;
}

function fn_get_calendar_delivery_period($period_start, $period_finish, $period_step)
{
    if (!$period_step) {
        return [];
    }

    list($start_hour, $start_minute) =
        (strpos($period_start, ':') !== false)
        ? explode(':', $period_start)
        : [$period_start, '00'];

    list($end_hour, $end_minute) = 
        (strpos($period_finish, ':') !== false)
        ? explode(':', $period_finish)
        : [$period_finish, '00'];

    list($period) = 
        (strpos($period_step, ':') !== false)
        ? explode(':', $period_step)
        : [$period_step];

    $periods = [];

    while ($start_hour < $end_hour) {
        $end_period = $start_hour + $period;

        if ($end_period < $end_hour) {
            $data = ($start_hour < 10 ? '0' : '') . $start_hour . ':' . $start_minute . '-' . $end_period . ':' . $start_minute;
        } else {
            // last
            $data = ($start_hour < 10 ? '0' : '') . $start_hour . ':' . $start_minute . '-' . $end_hour . ':' . $end_minute;
        }

        $periods[$data] = [
            'value' => $data,
            'hour' => $start_hour,
        ];

        $start_hour = $end_period;
    }


    return $periods;
}

/**
 * convert array weekdays to string
 *
 * Convert array weekdays data to string weekdays
 *
 * First elm is a sunday
 *
 * @param array $days_array list of active day array
 * @return string
 **/
function fn_delivery_date_to_line(array $days_array = [])
{
    $days_str = '';
    for ($i=0; $i <= 6; $i++) {
        $days_str[$i] = in_array($i, $days_array) ? '1' : '0';
    }
    return (string) $days_str;
}

/**
 * convert string weekdays to array
 *
 * Convert string weekdays data to array weekdays only with select days
 *
 * First elm is a sunday
 *
 * @param string $days_str list of active day array
 * @return array
 **/
function fn_delivery_date_from_line(string $days_str = '')
{
    $days_array = [];
    for ($i=0; $i < strlen($days_str); $i++) {
        if ($days_str[$i]) {
            $days_array[] = $i;
        }
    }
    return (array) $days_array;
}
