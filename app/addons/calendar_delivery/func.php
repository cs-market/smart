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
}

function fn_calendar_delivery_exim1c_order_xml_pre(&$order_xml, $order_data, $cml) {
    if (isset($order_data['delivery_date']) && !empty($order_data['delivery_date'])) {
        $order_xml[$cml['value_fields']][][$cml['value_field']] = array(
            $cml['name'] => $cml['delivery_date'],
            $cml['value'] => fn_date_format($order_data['delivery_date'], Registry::get('settings.Appearance.date_format'))
        );
    }
}

function fn_calendar_delivery_get_companies($params, &$fields, $sortings, $condition, $join, $auth, $lang_code, $group) {
    $fields[] = 'tomorrow_rule';
    $fields[] = 'tomorrow_timeslot';
    $fields[] = 'sunday_shipping';
    $fields[] = 'saturday_rule';
    // backward compatibility
    $fields[] = 'tomorrow_rule as after17rule';
}

// backward compatibility
function fn_calendar_delivery_get_company_data($company_id, $lang_code, $extra, &$fields, $join, $condition) {
    $fields[] = 'tomorrow_rule as after17rule';
}

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
    }
    // backward compatibility with mobile app
    if (is_array($order['delivery_date'])) {
        $order['delivery_date'] = array_shift($order['delivery_date']);
    }
    if (!empty($order['delivery_date'])) {
        $order['delivery_date'] = fn_parse_date_check_year($order['delivery_date']);
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

}

function fn_calendar_delivery_form_cart_pre_fill($order_id, &$cart, $auth, $order_info) {
    if (isset($order_info['delivery_date']))     $cart['delivery_date'] = $order_info['delivery_date'];
}


/// TEMP
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