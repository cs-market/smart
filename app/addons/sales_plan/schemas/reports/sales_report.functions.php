<?php

use Tygh\Registry;

require_once(Registry::get('config.dir.functions') . 'fn.sales_reports.php');

function fn_generate_sales_report($params) {
    $default_params = array(
        'delimiter' => 'S',
        'period' => 'custom',
        'time_from' => strtotime("-1 month"),
        'time_to' => TIME,
        'filename' => 'sales_report_' . date('dMY_His', TIME) . '.csv',
        'show_null' => true,
        'group_by' => 'day'
    );
        
    if (isset($params['period']) && $params['period'] == 'A') unset($params['period']);
    list($params['time_from'], $params['time_to']) = fn_create_periods($params);
    if (empty($params['time_from'])) unset($params['time_from']);
    if (empty($params['time_to'])) unset($params['time_to']);

    if ($params['time_to'] > time()) $params['time_to'] = time();

    if (is_array($params)) {
        $params = array_merge($default_params, $params);
    } else {
        $params = $default_params;
    }

    if (Registry::get('runtime.company_id')) {
        $params['company_id'] = Registry::get('runtime.company_id');
    }

    $output = array();
    if (isset($params['is_search'])) {
        $key_function = is_callable("fn_ts_this_" . $params['group_by']) ? "fn_ts_this_" . $params['group_by'] : 'fn_ts_this_day';

        $interval_id = db_get_field('SELECT interval_id FROM ?:sales_reports_intervals WHERE interval_code = ?s', $params['group_by']);

        $intervals = fn_check_intervals($interval_id, $params['time_from'], $params['time_to']);
        if ($interval_id == 5) {
            foreach ($intervals as &$interval) {
                $interval['description'] = date('d.m.Y', $interval['time_from']);
            }
        }
        unset($interval);

        $elements_fields = $elements_condition = $elements_join = array();
        $elements_group = '';

        $elements_fields['default'] = 'u.user_id, u.firstname';
        $elements_condition['default'] = db_quote(' AND u.status = ?s AND u.user_type = ?s', 'A', 'C');

        if (!empty($params['user_ids'])) {
            $elements_condition['user_id'] = db_quote(' AND u.user_id IN (?a)', explode(',', $params['user_ids']));
        }

        fn_set_hook('generate_sales_report', $params, $elements_join, $elements_condition);

        if (!empty($params['usergroup_id'])) {
            $elements_join['usergroup_links'] .= db_quote(" LEFT JOIN ?:usergroup_links AS ul ON ul.user_id = u.user_id AND ul.usergroup_id = ?i", $params['usergroup_id']);
            $elements_condition['usergroup_links'] = " AND ul.status = 'A'";
        }
        if (!empty($params['company_id'])) {
            list($users, ) = fn_get_users(array('company_id' => $params['company_id']), $_SESSION['auth']);
            if (!empty($users)) $elements_condition['company_user_id'] = db_quote(' AND u.user_id IN (?a)', fn_array_column($users, 'user_id'));
        }
        if ($params['with_purchases'] == 'Y') {
            $elements_join['orders'] = ' LEFT JOIN ?:orders AS o ON o.user_id = u.user_id ';
            $elements_condition['orders'] = db_quote(' AND o.timestamp BETWEEN ?i AND ?i', $params['time_from'], $params['time_to']);
            $elements_group = ' GROUP BY o.user_id';
        }
        if ($params['show_plan'] == 'Y') {
            $elements_fields['sales_plan'] = 'sp.amount_plan, sp.frequency';
            $elements_join['sales_plan'] = ' LEFT JOIN ?:sales_plan AS sp ON sp.user_id = u.user_id AND sp.company_id = u.company_id';
        }

        $elements = db_get_array(
            "SELECT " . implode(', ', $elements_fields)
            . " FROM ?:users as u"
            . implode(' ', $elements_join)
            . " WHERE 1 " . implode(' ', $elements_condition)
            . $elements_group
            . " ORDER BY firstname ASC, user_id"
        );

        $time_condition = db_quote(" timestamp BETWEEN ?i AND ?i", $params['time_from'], $params['time_to']);
        $group_condition = ' GROUP BY `interval`';
        if ($params['group_by'] == 'year') {
            $add_field = db_quote(", DATE_FORMAT(FROM_UNIXTIME(timestamp), '%Y') as `interval`, timestamp");
        } elseif ($params['group_by'] == 'month') {
            $add_field = db_quote(", DATE_FORMAT(FROM_UNIXTIME(timestamp), '%Y-%m') as `interval`, timestamp");
        } elseif ($params['group_by'] == 'week') {
            $add_field = db_quote(", DATE_FORMAT(FROM_UNIXTIME(timestamp), '%Y-%m-%u') as `interval`, timestamp");
        } elseif ($params['group_by'] == 'day') {
            $add_field = db_quote(", DATE_FORMAT(FROM_UNIXTIME(timestamp), '%Y-%m-%d') as `interval`, timestamp");
        } else {
            $add_field = db_quote(", 1 as `interval`, `timestamp`");
            $group_condition = '';
        }

        foreach ($elements as $element) {
            // add company_id condition
            if (!empty($params['company_id'])) {
                $company_condition = db_quote(' AND ?:orders.company_id = ?i', $params['company_id']);
            }
            $fact = db_get_hash_array("SELECT SUM(total) as total $add_field, count(order_id) as count FROM ?:orders WHERE user_id = ?i AND $time_condition AND ?:orders.status != 'T' AND ?:orders.status != 'I' AND ?:orders.is_parent_order != 'Y' $company_condition $group_condition", 'interval', $element['user_id']);

            if ($params['only_zero'] == 'Y' && count($fact) == count($intervals)) {
                continue;
            }

            $plan = array(0);
            if ($params['show_plan'] == 'Y' && $element['frequency']) {
                $base_timestamp = max(fn_array_column($fact, 'timestamp'));

                $element['frequency_ts'] = $element['frequency'] * SECONDS_IN_DAY;

                $ts = $base_timestamp;
                while ($params['time_from'] <= $ts) {
                    if ($params['time_to'] >= $ts) {
                        $key = call_user_func($key_function, $ts);
                        $plan[$key] += $element['amount_plan'];
                    }
                    $ts -= $element['frequency_ts'];
                }

                $ts = $base_timestamp += $plan['frequency_ts'];
                while ($params['time_from'] <= $ts) {
                    if ($params['time_to'] >= $ts) {
                        $key = call_user_func($key_function, $ts);
                        $plan[$key] += $element['amount_plan'];
                    }
                    $ts -= $element['frequency_ts'];
                }
            }

            $user = fn_get_user_info($element['user_id']);
            $row = array();
            $row[__('company_name')] = ($user['company_id']) ? fn_get_company_name($user['company_id']) : '-';
            $row[__('date')] = date('d.m.Y', $user['timestamp']);
            $row[__('customer')] = $user['firstname'] . (($params['show_user_id'] == 'Y') ? ' #' . $element['user_id'] : '');//fn_get_user_name($plan['user_id']);

            fn_set_hook('generate_sales_report_post', $params, $row, $user);

            $row[__('address')] = $user['s_address'];
            $row[__('code')] = !empty($user['fields'][39]) ? $user['fields'][39] : $user['fields'][38];
            foreach ($intervals as $interval) {
                $f = $interval['description'];
                if ($params['show_plan'] == 'Y') {
                    $p = __('plan') . ' ' . $f;
                    $f = __('fact') . ' ' . $f;
                    if (!empty($plan)) {
                        foreach ($plan as $ts => $value) {
                            if ($ts >= $interval['time_from'] && $ts <= $interval['time_to']) {
                                $row[$p] = $value;
                                break;
                            }
                        }
                    }
                    if (!isset($row[$p])) {
                        $row[$p] = 0;
                    }
                }

                if (!empty($fact)) {
                    foreach ($fact as $interval_data) {
                        if ($interval_data['timestamp'] >= $interval['time_from'] && $interval_data['timestamp'] <= $interval['time_to']) {
                            $row[$f] = $interval_data['total'];
                            break;
                        }
                    }
                }

                if (!isset($row[$f])) {
                    $row[$f] = 0;
                }
            }

            if ($params['summ'] == 'Y') {
                $f = array_sum(fn_array_column($fact, 'total'));
                $p = array_sum($plan);

                if ($params['show_plan'] == 'Y') {
                    $row[__('total') . ' ' . __('fact')] = $f;
                    $row[__('total') . ' ' . __('plan')] = $p;
                    $row[__('total') . ' %'] = ($p) ? round($f/$p*100) : 0;
                } else {
                    $row[__('total')] = $f;
                }
            }
            if ($params['amount'] == 'Y') {
                $f = array_sum(fn_array_column($fact, 'count'));
                $row[__('quantity')] = $f;
            }

            $output[] = $row;
        }
    }

    return array($output, $params);
}

