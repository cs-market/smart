<?php

use Tygh\Registry;

function fn_generate_category_report($params) {
    $default_params = array(
        'delimiter' => 'S',
        'period' => 'custom',
        'time_from' => strtotime("-3 month"),
        'time_to' => TIME,
        'filename' => 'category_report_' . date('dMY_His', TIME) . '.csv',
        // 'hide_zero' => "Y"
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

    if (!empty($params['user_ids']) && !is_array($params['user_ids'])) {
        $params['user_ids'] = explode(',', $params['user_ids']);
    }

    $output = $output2 = $data = $keys = array();
    if (isset($params['is_search'])) {
        //$o
        $o_params = $params;
        $group_by = (isset($params['group_by'])) ? $params['group_by'] : 'day';
        $key_function = is_callable("fn_ts_this_" . $group_by) ? "fn_ts_this_" . $group_by : "fn_ts_this_day";
        // check managers, ugroup, company_id
        list($orders, $c_params, $totals) = fn_get_orders($o_params);

        $orders = fn_array_elements_to_keys($orders, 'order_id');
        $orders_info = db_get_hash_array('SELECT p.product_id, p.order_id, pc.category_id FROM ?:order_details AS p RIGHT JOIN ?:products_categories AS pc ON pc.product_id = p.
                product_id AND pc.link_type = ?s WHERE p.order_id IN (?a)', 'order_id' , 'M', array_keys($orders));
        foreach ($orders_info as $order_id => &$data) {
            $data['timestamp'] = $key_function($orders[$order_id]['timestamp']);
            $data['user_id'] = $orders[$order_id]['user_id'];
        }
        $periods = array_unique(fn_array_column($orders_info, 'timestamp'));
        sort($periods);
        $user_categories_ts_products = fn_group_array_by_key($orders_info, 'user_id', 'category_id', 'timestamp', 'product_id');
        foreach ($user_categories_ts_products as $user_id => &$categories_ts_products) {
            $usergroup_ids = fn_define_usergroups(array('user_id' => $user_id), 'C');
            $ud_condition = 'AND (' . fn_find_array_in_set($usergroup_ids, 'p.usergroup_ids', true) . ')' . db_quote(' AND p.status = ?s', 'A');
            $ud_condition .= ' AND (' . fn_find_array_in_set($usergroup_ids, 'c.usergroup_ids', true) . ')' . db_quote(' AND c.status = ?s', 'A');
            $available_categories = db_get_hash_single_array("SELECT count(distinct(p.product_id)) AS count, pc.category_id FROM ?:products AS p LEFT JOIN ?:products_categories AS pc ON pc.product_id = p.product_id AND pc.link_type = ?s LEFT JOIN ?:categories as c ON pc.category_id = c.category_id WHERE 1 $ud_condition GROUP BY pc.category_id ", array('category_id', 'count'), 'M');
            foreach ($categories_ts_products as $category_id => &$ts_products) {
                foreach ($ts_products as $ts => &$p) {
                    $p = count($p);
                }
                foreach ($periods as $period) {
                    if (isset($ts_products[$period]) && isset($available_categories[$category_id])) {
                        
                        $o[date('d.m.Y', $period)] = round($ts_products[$period] / $available_categories[$category_id] * 100) . '%';
                    } else {
                        $o[date('d.m.Y', $period)] = '0%';
                    }
                }
                $user = fn_get_user_info($user_id);
                $output[] = array_merge(array(
                    __('customer') => $user['firstname'],
                    __('address') => $user['b_address'],
                    __('code') => !empty($user['fields'][39]) ? $user['fields'][39] : (!empty($user['fields'][38]) ? $user['fields'][38] : '') ,
                    __('category') => fn_get_category_name($category_id),
                ), $o);
            }
        }
    }

    return array($output, $params);
}
