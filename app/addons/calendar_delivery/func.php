<?php

use Tygh\Languages\Languages;
use Tygh\Languages\Values;
use Tygh\Registry;
use Tygh\Enum\YesNo;

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
    if (isset($additional_data[DOCUMENT_ORIGINALS])) {
        $order['documents_originals'] = true;
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

function fn_calendar_get_nearest_delivery_day($shipping_params = [], $get_ts = false) {
    $nearest_delivery = $shipping_params['nearest_delivery'] ?? 0;
    if (!empty($shipping_params['working_time_till']) && strtotime(date("G:i")) >= strtotime($shipping_params['working_time_till'])) {
        $nearest_delivery += 1;
    }

    if (!empty($shipping_params['exception_days']) && in_array(getdate()['wday'], fn_delivery_date_from_line($shipping_params['exception_days'])) && strtotime(date("G:i")) <= strtotime($shipping_params['exception_time_till'])) {
        $nearest_delivery = 0;
    }

    if (!YesNo::toBool($shipping_params['monday_rule']) && in_array(getdate()['wday'], [0,6])) {

        $now = new \DateTime('today');
        $monday = new \DateTime('next tuesday');
        $diff = $now->diff($monday)->d;

        $nearest_delivery = ($diff > $nearest_delivery) ? $diff : $nearest_delivery;
    }

    return $nearest_delivery;
}

function fn_calendar_delivery_pre_update_order(&$cart, $order_id) {
    if (count($cart['product_groups']) == 1) {
        $group = reset($cart['product_groups']);
        if (!empty($group['delivery_date'])) $cart['delivery_date'] = fn_parse_date($group['delivery_date']);
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

    if (count($cart['product_groups']) == 1) {
        if (isset(reset($cart['product_groups'])['documents_originals']) && YesNo::toBool(reset($cart['product_groups'])['documents_originals'])) {
            $order_data = array(
                'order_id' => $order_id,
                'type' => DOCUMENT_ORIGINALS,
                'data' => "Y"
            );
            db_query("REPLACE INTO ?:order_data ?e", $order_data);
        }
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

function fn_calendar_delivery_update_user_profile_pre($user_id, $user_data, $action) {
    if (!empty($user_data['delivery_date_by_storage'])) {
        foreach ($user_data['delivery_date_by_storage'] as $key => &$user_storage_data) {
            if (empty($user_storage_data['storage_id']) || empty($user_storage_data['delivery_date'])) {
                unset($user_data['delivery_date_by_storage'][$key]);
                continue;
            }
            $user_storage_data['user_id'] = $user_id;
            if (is_array($user_storage_data['delivery_date'])) {
                $user_storage_data['delivery_date'] = fn_delivery_date_to_line($user_storage_data['delivery_date']);
            }
        }
        $user_data['delivery_date_by_storage'] = fn_array_elements_to_keys($user_data['delivery_date_by_storage'], 'storage_id');
        
        db_query('DELETE FROM ?:user_storages WHERE user_id = ?i', $user_id);
        if (!empty($user_data['delivery_date_by_storage'])) db_query('INSERT INTO ?:user_storages ?m', $user_data['delivery_date_by_storage']);
    }
}

function fn_calendar_delivery_get_user_info($user_id, $get_profile, $profile_id, &$user_data) {
    $user_data['delivery_date_by_storage'] = db_get_hash_array('SELECT * FROM ?:user_storages WHERE user_id = ?i ORDER BY storage_id', 'storage_id', $user_id);
}

function fn_calendar_delivery_get_user_short_info_pre($user_id, &$fields, $condition, $join, $group_by) {
    $fields[] = 'delivery_date';
}

function fn_calendar_delivery_user_init(&$auth, &$user_info) {
    if (!empty($auth['user_id'])) {
        // user_info is empty in API
        $auth['delivery_date_by_storage'] = $user_info['delivery_date_by_storage'] = db_get_hash_array('SELECT * FROM ?:user_storages WHERE user_id = ?i ORDER BY storage_id', 'storage_id', $auth['user_id']);
    }
}

function fn_calendar_delivery_calculate_cart_content_after_shipping_calculation($cart, $auth, $calculate_shipping, $calculate_taxes, $options_style, $apply_cart_promotions, $lang_code, $area, $cart_products, &$product_groups) {
    if (!empty($auth['user_id'])) {
        foreach($product_groups as $group_id => &$group) {
            $calendar_shippings = array_filter($group['shippings'], function($v) {return (isset($v['service_code']) && $v['service_code'] == 'calendar');});
            if (!empty($calendar_shippings)) {
                $delivery_dates = [];

                // user_info is empty in API. user_info wrong in backend :)
                //if (!$user_info = Registry::get('user_info')) {
                    $user_info = fn_get_user_short_info($auth['user_id']);
                    $user_info['delivery_date_by_storage'] = db_get_hash_array('SELECT * FROM ?:user_storages WHERE user_id = ?i', 'storage_id', $auth['user_id']);
                //}

                $user_weekdays = $user_info['delivery_date'];

                if (!empty($group['storage_id']) && !empty($user_info['delivery_date_by_storage'][$group['storage_id']]['delivery_date'])) {
                    $user_weekdays = $user_info['delivery_date_by_storage'][$group['storage_id']]['delivery_date'];
                }
                $delivery_dates = fn_delivery_date_from_line($user_weekdays);

                //TODO может начать кэшировать эти запросы в бд?
                $usergroup_working_time_till = db_get_row('SELECT working_time_till FROM ?:usergroups WHERE usergroup_id IN (?a) AND working_time_till != ""', Tygh::$app['session']['auth']['usergroup_ids']);

                //TODO TEMP!! удалить company_settings в середине 2022, надо бы настройки календаря переносить из вендора в шипинг
                $company_settings = db_get_row('SELECT nearest_delivery, working_time_till, saturday_shipping, sunday_shipping, monday_rule, period_start, period_finish, period_step FROM ?:companies WHERE company_id = ?i', $group['company_id']);

                $company_weekdays = '1111111';
                $company_weekdays[0] = YesNo::toBool($company_settings['sunday_shipping']) ? 1 : 0;
                $company_weekdays[6] = YesNo::toBool($company_settings['saturday_shipping']) ? 1 : 0;

                $storage_weekdays = '1111111';
                if (!empty($group['storage_id'])) {
                    $storage_settings = Registry::get('runtime.storages.'.$group['storage_id']);
                    $storage_weekdays[0] = YesNo::toBool($storage_settings['sunday_shipping']) ? 1 : 0;
                    $storage_weekdays[6] = YesNo::toBool($storage_settings['saturday_shipping']) ? 1 : 0;
                }

                $weekdays_availability = $company_weekdays & $storage_weekdays & $user_weekdays;

                foreach ($group['shippings'] as $shipping_id => &$shipping) {
                    if (isset($shipping['module']) && $shipping['module'] == 'calendar_delivery') {
                        if (isset($shipping['service_params']['limit_weekday']) && $shipping['service_params']['limit_weekday'] == 'C') {
                            $shipping['service_params']['customer_shipping_calendar'] = $delivery_dates;
                        }
                        // TODO грохнуть это к 2023 году так как повсеместно передодим на nearest_delivery_day weekdays_availability
                        $shipping['service_params'] = fn_array_merge($shipping['service_params'], $company_settings, $storage_settings, $usergroup_working_time_till);

                        $shipping['service_params']['nearest_delivery_day'] = fn_calendar_get_nearest_delivery_day($shipping['service_params']);
                        $shipping['service_params']['weekdays_availability'] = strrev($weekdays_availability);
                    }
                }
            }
        }
    }
    unset($group, $shipping);
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
function fn_delivery_date_from_line($days_str = '')
{
    if (!is_string($days_str)) return [];
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

function fn_calendar_delivery_allow_place_order_post(&$cart, $auth, $parent_order_id, $total, &$result) {
    if (Registry::get('runtime.mode') != 'checkout') {
        foreach ($cart['product_groups'] as $group_id => &$group) {
            $res = true;
            if ($group['chosen_shippings'][0]['module'] != 'calendar_delivery') {
                continue;
            }

            if (!($cart_delivery_day = $cart['delivery_date'][$group_id])) {
                $cart_delivery_day = reset($cart['delivery_date']);
            }

            if (!empty($cart['documents_originals'][$group_id])) {
                $group['documents_originals'] = $cart['documents_originals'][$group_id];
                unset($cart['documents_originals'][$group_id]);
            }

            // backward compatibility (for mobile app)
            $group['delivery_date'] = $group['delivery_date'] ?? $cart_delivery_day;

            if (is_array($group['delivery_date'])) $group['delivery_date'] = array_shift($group['delivery_date']);

            if (empty($group['delivery_date'])) {
                $res = false;
            } else {
                $choosed_ts = fn_parse_date($group['delivery_date']);
                $nearest_delivery = $group['chosen_shippings'][0]['service_params']['nearest_delivery_day'];
                $ts = ($nearest_delivery) ? strtotime("+$nearest_delivery days") : time();
                $compare_ts = fn_ts_this_day($ts);
                if ($choosed_ts < $compare_ts) {
                    $res = false;
                }

                if (!empty($group['chosen_shippings'][0]['service_params']['weekdays_availability'])) {
                    $weekdays_availability = $group['chosen_shippings'][0]['service_params']['weekdays_availability'];
                    $weekday = 1 << getdate($choosed_ts)['wday'];
                    if (!(bindec($weekdays_availability) & $weekday)) {
                        $res = false;   
                    }
                } else {
                    $res = false;
                }
            }

            if (!$res &&  AREA != A) {
                $result = false;
                fn_set_notification('E', __('error'), __('calendar_delivery.choose_another_day'));
                return;
            }
        }
    }
}

function fn_calendar_delivery_update_storage_pre(&$storage_data, $storage_id) {
    if (is_array($storage_data['exception_days'])) {
        $storage_data['exception_days'] = fn_delivery_date_to_line($storage_data['exception_days']);
    }
}

function fn_calendar_delivery_get_storages_post(&$storages, $params) {
    foreach($storages as &$storage) {
        //$storage['exception_days'] = fn_delivery_date_from_line($storage['exception_days']);
    }
}

function fn_calendar_delivery_delete_storages($storage_ids) {
    db_query("DELETE FROM ?:user_storages WHERE storage_id IN (?n)", $storage_ids);
}

function fn_calendar_delivery_post_delete_user($user_id, $user_data, $result) {
    if ($result) db_query("DELETE FROM ?:user_storages WHERE user_id = ?i", $user_id);
}
