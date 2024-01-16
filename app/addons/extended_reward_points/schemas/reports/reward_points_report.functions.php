<?php

use Tygh\Registry;
use Tygh\Enum\YesNo;

function fn_generate_reward_points_report($params) {
    $default_params = array(
        'delimiter' => 'S',
        'company_id' => '45',
        'period' => 'custom',
        'time_from' => strtotime("-3 month"),
        'time_to' => TIME,
        'filename' => date('dMY_His', TIME) . '.csv',
    );

    $params = array_filter($params);
    if (isset($params['period']) && $params['period'] == 'A') unset($params['period']);

    if (is_array($params)) {
        $params = array_merge($default_params, $params);
    } else {
        $params = $default_params;
    }

    list($params['time_from'], $params['time_to']) = fn_create_periods($params);

    if (Registry::get('runtime.company_id')) {
        $params['company_id'] = Registry::get('runtime.company_id');
    }

    $output = array();
    if (isset($params['is_search'])) {
        $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true, false, CART_LANGUAGE);
        $grant_reward_points_statuses = array_filter($order_statuses, function($v) {
            return (isset($v['params']['grant_reward_points']) && $v['params']['grant_reward_points'] == YesNo::YES);
        });

        list($orders, $c_params, $totals) = fn_get_orders($params);

        $ttl = db_get_hash_single_array('SELECT ttl, order_id FROM ?:reward_point_details WHERE order_id IN (?a)', array('order_id', 'ttl'), array_column($orders, 'order_id'));
        $points_info = db_get_hash_single_array('SELECT data, order_id FROM ?:order_data WHERE order_id IN (?a) AND type = ?s', array('order_id', 'data'), array_column($orders, 'order_id'), POINTS_IN_USE);
        $users_data = db_get_hash_array('SELECT u.user_id, u.user_login, p.s_state, d.data FROM ?:users AS u LEFT JOIN ?:user_profiles AS p ON u.user_id = p.user_id LEFT JOIN ?:user_data AS d ON d.user_id = u.user_id AND d.type = ?s WHERE u.user_id IN (?a)', 'user_id', POINTS, array_column($orders, 'user_id'));

        if ($orders) {
            foreach ($orders as $order) {
                $output[] = [
                    __('region') => $users_data[$order['user_id']]['s_state'] ?? '',
                    __('login') => $users_data[$order['user_id']]['user_login'],
                    __('order') => $order['order_id'],
                    __('order_date') => fn_date_format($order['timestamp'], Registry::get('settings.Appearance.date_format')),
                    __('earned_points') => (in_array($order['status'], array_keys($grant_reward_points_statuses)) && !empty($order['points'])) ? $order['points'] : 0,
                    __('extended_reward_points.reward_points_ttl') => (!empty($ttl[$order['order_id']])) ? fn_date_format($ttl[$order['order_id']], Registry::get('settings.Appearance.date_format')) : '-',
                    __('points_in_use') => (!empty($points_info[$order['order_id']])) ? unserialize($points_info[$order['order_id']])['points'] : 0,
                    __('extended_reward_points.user_points') => (!empty($users_data[$order['user_id']]['data'])) ? unserialize($users_data[$order['user_id']]['data']) : 0
                ];
            }
        }
    }
    return array($output, $params);
}
