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

function fn_calendar_delivery_calculate_cart_taxes_pre(&$cart, $cart_products, $product_groups, $calculate_taxes, $auth) {
    // if (!empty($cart['chosen_shipping']) && (!isset($cart['delivery_date']) || empty($cart['delivery_date']))) {
    //     $companies = fn_array_column($cart_products, 'company_id');
    //     foreach ($companies as $company_id) {
    //         $ts = fn_calendar_get_nearest_delivery_day(fn_get_company_data($company_id), true);
    //         if (Registry::get('settings.Appearance.calendar_date_format') == "month_first") {
    //             $cart['delivery_date'][$company_id] = date('m/d/Y', $ts);
    //         } else {
    //             $cart['delivery_date'][$company_id] = date('d/m/Y', $ts);
    //         }
    //     }
    // }
}

function fn_calendar_delivery_get_orders($params, $fields, $sortings, &$condition, $join, $group) {
    if (isset($params['delivery_date']) && !empty($params['delivery_date'])) {
        $condition .= db_quote(' AND (?:orders.delivery_date = ?i OR ?:orders.delivery_date = 0)', $params['delivery_date']);
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

function fn_calendar_get_nearest_delivery_day($shipping_params, $get_ts = false) {
    $nearest_delivery = $shipping_params['nearest_delivery'] ?? 0;
    if (!empty($shipping_params['working_time_till']) && strtotime(date("G:i")) >= strtotime($shipping_params['working_time_till'])) {
        $nearest_delivery += 1;
    }

    return $nearest_delivery;
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
    }
    if (is_array($order['delivery_period'])) {
        $order['delivery_period'] = array_shift($order['delivery_period']);
    }
    // 16.03.2021. strpos temporary for mobile app! Need to be removed.
    if (!empty($order['delivery_date']) && !strpos($order['delivery_date'], 'undefined')) {
        $order['delivery_date'] = fn_parse_date($order['delivery_date']);
    } else {
        unset($order['delivery_date']);
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
    if (isset($user_data['delivery_date']) && is_array($user_data['delivery_date'])) {
        $user_data['delivery_date'] = fn_delivery_date_to_line($user_data['delivery_date']);
    }
}

function fn_calendar_delivery_get_user_info($user_id, $get_profile, $profile_id, &$user_data) {
    $user_data['delivery_date'] = fn_delivery_date_from_line($user_data['delivery_date']);
}

function fn_calendar_delivery_calculate_cart_content_after_shipping_calculation($cart, $auth, $calculate_shipping, $calculate_taxes, $options_style, $apply_cart_promotions,
$lang_code, $area, $cart_products, &$product_groups) {
    foreach($product_groups as $group_id => &$group) {
        if (isset(Tygh::$app['session']['auth']['user_id']) && !empty(Tygh::$app['session']['auth']['user_id'])) {
            $calendar_shippings = array_filter($group['shippings'], function($v) {return (isset($v['service_code']) && $v['service_code'] == 'calendar');});
            if (!empty($calendar_shippings)) {
                $user_limited_calendar_shippings = array_filter($calendar_shippings, function($v) {return (isset($v['service_params']['limit_weekday']) && $v['service_params']['limit_weekday'] == 'C');});

                $delivery_dates = [];
                if (!empty($user_limited_calendar_shippings)) {
                    $delivery_dates = fn_get_customer_delivery_dates(Tygh::$app['session']['auth']['user_id']);
                    $delivery_dates = fn_delivery_date_from_line($delivery_dates);
                }

                $usergroup_working_time_till = db_get_row('SELECT working_time_till FROM ?:usergroups WHERE usergroup_id IN (?a) AND working_time_till != ""', Tygh::$app['session']['auth']['usergroup_ids']);

                //TODO TEMP!! удалить company_settings в середине 2022, надо бы настройки календаря переносить из вендора в шипинг
                $company_settings = db_get_row('SELECT nearest_delivery, working_time_till, saturday_shipping, sunday_shipping, monday_rule, period_start, period_finish, period_step FROM ?:companies WHERE company_id = ?i', $group['company_id']);

                foreach ($group['shippings'] as $shipping_id => &$shipping) {
                    if (isset($shipping['module']) && $shipping['module'] == 'calendar_delivery') {
                        if (isset($shipping['service_params']['limit_weekday']) && $shipping['service_params']['limit_weekday'] == 'C') {
                            $shipping['service_params']['customer_shipping_calendar'] = $delivery_dates;
                        }
                        $shipping['service_params'] = fn_array_merge($shipping['service_params'], $company_settings, $usergroup_working_time_till);

                        $shipping['service_params']['nearest_delivery_day'] = fn_calendar_get_nearest_delivery_day($shipping['service_params']);
                    }
                }
            }
        }
        unset($group, $shipping);
    }
}

function fn_calendar_delivery_get_usergroups($params, $lang_code, &$field_list, $join, $condition, $group_by, $order_by, $limit) {
    $field_list .= ', a.working_time_till';
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
    $days_str = '0000000';
    foreach ($days_array as $i) {
        $days_str[$i] = 1;
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
 * @param string $days_str list of day
 * @return array
 **/
function fn_delivery_date_from_line(string $days_str = '')
{
    return array_keys(array_filter(str_split($days_str)));
}

/**
 * add calendar disalow days
 *
 * @param array $arr disalow days
 * @param string $day
 * @return array
 **/
function fn_add_calendar_disalow_days($arr, $day)
{
    $arr[] = $day;
    return $arr;
}

/**
 * get customer delivery dates
 *
 * @param int $user_id User ID
 * @return array
 **/
function fn_get_customer_delivery_dates($user_id)
{
    return db_get_field("SELECT delivery_date FROM ?:users WHERE user_id = ?i", $user_id);
}
