<?php

use Tygh\Registry;

function fn_generate_unsold_report($params) {
    $default_params = array(
        'delimiter' => 'S',
        'period' => 'custom',
        'time_from' => strtotime("-1 week"),
        'time_to' => TIME,
        'filename' => 'unsold_report_' . date('dMY_His', TIME) . '.csv',
        'summ' => 0,
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
        $users_params = $product_usergroups =$category_usergroups = $c_usergroups = array();
        if (isset($params['product_ids'])) {
            $product_ids = explode(',', $params['product_ids']);
            $condition .= db_quote(' AND product_id in (?a)', $product_ids);
            list($products, ) = fn_get_products(['pid' => $product_ids]);
            $product_usergroups = array_column($products, 'usergroup_ids');
            $category_ids = array_column($products, 'main_category');
            $category_usergroups = db_get_fields('SELECT usergroup_ids FROM ?:categories WHERE category_id IN (?a)', $category_ids);
        }
        if ($params['category_ids']) {
            $c_usergroups = db_get_fields('SELECT usergroup_ids FROM ?:categories WHERE category_id IN (?a)', explode(',',$params['category_ids']));
            list($products ) = fn_get_products(['cid' => explode(',',$params['category_ids'])]);
            $condition .= db_quote(' AND product_id in (?a)', array_keys($products));

        }
        $usergroup_ids = array_merge($product_usergroups, $category_usergroups, $c_usergroups);
        if (!empty($usergroup_ids)) $users_params['usergroup_ids'] = array_unique(explode(',', implode(',', $usergroup_ids)));
        if (isset($params['time_from'])) {
            $condition .= db_quote(' AND o.timestamp > ?i', $params['time_from']);
        }
        if (isset($params['time_to'])) {
            $condition .= db_quote(' AND o.timestamp < ?i', $params['time_to']);
        }

        if (isset($params['user_ids'])) {
            $condition .= db_quote(' AND user_id in (?a)', explode(',', $params['user_ids']));  
        }

        if (isset($params['user_ids'])) {
            $users_params['user_id'] = explode(',', $params['user_ids']);
        }
        
        $purchased_users = db_get_hash_array("SELECT user_id, sum(price * amount) as total, o.order_id FROM ?:order_details AS od LEFT JOIN ?:orders AS o ON o.order_id = od.order_id WHERE 1 $condition GROUP BY user_id", 'user_id');

        if (isset($params['summ'])) {
            $purchased_more_users = array_filter($purchased_users, function($val) use ($params) {
                return ($val['total'] >= $params['summ']);
            });
        }

        if (isset($params['hide_null']) && ($params['hide_null'] == 'Y') && (isset($params['summ']))) {
            $purchased_less_users = array_filter($purchased_users, function($val) use ($params) {
                return ($val['total'] < $params['summ']);
            });
            if ($purchased_less_users) {
                $users_params['user_id'] = array_keys($purchased_less_users);
            } else {
                return array($output, $params);
            }
        }
        list($users, ) = fn_get_users($users_params, $_SESSION['auth']);
        $users = fn_array_value_to_key($users, 'user_id');
        

        $result_users = array_diff_key($users, $purchased_more_users);
        if (!empty($params['export'])) {
            return array(array_keys($result_users), $params);
        }

        foreach ($result_users as $key => $u) {
            $user = fn_get_user_info($u['user_id']);
            $output[] = array(
                __('user') => (!empty(trim($user['firstname'])) ? $user['firstname'] : $user['email'])  . " " .  "#" . $user['user_id'],
                __('address') => $user['b_address'],
                __('code') => !empty($user['fields'][39]) ? $user['fields'][39] : $user['fields'][38],
                __('sales') => ($purchased_users[$key]['total']) ? $purchased_users[$key]['total'] : 0
            );
        }
    }
    return array($output, $params);
}
