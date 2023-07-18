<?php

use Tygh\Registry;
use Tygh\Enum\YesNo;
use Tygh\Enum\Addons\Returns\ReturnOperationStatuses;

defined('BOOTSTRAP') or die('Access denied');

function fn_returns_get_products($params, $fields, $sortings, &$condition, &$join, $sorting, $group_by, $lang_code, $having) {
    if (isset($params['only_ordered']) && $params['only_ordered']) {
        $join .= db_quote(' LEFT JOIN ?:order_details AS od ON od.product_id = products.product_id LEFT JOIN ?:orders AS o ON o.order_id = od.order_id ');
        $condition .= db_quote(' AND o.user_id = ?i ', $_SESSION['auth']['user_id']);
    }
}

function fn_create_return($products_data, $auth) {
    $return_id = false;
    fn_set_hook('pre_add_to_cart', $products_data, $cart, $auth, $update);
    $products_data = array_filter($products_data, function($v) {return $v['amount'];});
    if (!empty($products_data)) {
        $approve = db_get_field('SELECT approve_returns FROM ?:users WHERE user_id = ?i', $auth['user_id']);

        $total = 0;
        foreach ($products_data as $key => $data) {
            $product_id = (!empty($data['product_id'])) ? intval($data['product_id']) : intval($key);
            $price = fn_get_product_price($product_id, $data['amount'], $auth);
            $total += $price * $data['amount'];
        }

        $return_data = [
            'user_id' => $auth['user_id'],
            'timestamp' => strtotime("+1 day"),
            'company_id' => $auth['company_id'],
            'total' => $total,
            'comment' => '',
            'status' => YesNo::toBool($approve) ? ReturnOperationStatuses::APPROVED : ReturnOperationStatuses::REQUESTED,
        ];

        $return_id = db_query('INSERT INTO ?:returns ?e', $return_data);

        foreach ($products_data as &$product) {
            $product['return_id'] = $return_id;
            db_query('INSERT INTO ?:return_products ?e', $product);
        }
        unset($product);
    }

    fn_return_export_to_file($return_id);

    return $return_id;
}

function fn_get_returns($params, $items_per_page = 0) {
    $default_params = array(
        'page' => 1,
        'company_id' => Registry::get('runtime.company_id'),
        'get_items' => true,
        'items_per_page' => $items_per_page
    );

    if (is_array($params)) {
        $params = array_merge($default_params, $params);
    } else {
        $params = $default_params;
    }

    $condition = ' 1 ';

    if (isset($params['company_id']) && !empty($params['company_id'])) {
        $condition .= fn_get_company_condition('?:returns.company_id', true, $params['company_id']);
    }
    if (isset($params['return_id']) && !empty($params['return_id'])) {
        $condition .= db_quote(' AND return_id = ?i', $params['return_id']);
    }

    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(DISTINCT(?:returns.return_id)) FROM ?:returns WHERE ?p", $condition);
        $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
    }

    $returns = db_get_array("SELECT * FROM ?:returns WHERE ?p ORDER BY timestamp DESC $limit", $condition);

    foreach ($returns as &$return) {
        $return['user'] = fn_get_user_short_info($return['user_id']);
        $return['file_exists'] = false;
        if (!empty($return['user'])) {
            $user_code = is_numeric($return['user']['user_login']) ? $return['user']['user_login'] : $return['user']['email'];

            $return['file_path'] = $return['company_id'] . '/output/return.' . $user_code . '.#' . $return['return_id'] . '.csv';

            if (is_file(Registry::get('config.dir.files') . $return['file_path'])) {
                $return['file_exists'] = true;
            }
        }
    }

    if ($params['get_items']) {
        foreach ($returns as &$return) {
            $return['items'] = db_get_array('SELECT r.*, p.product_code FROM ?:return_products AS r LEFT JOIN ?:products AS p ON p.product_id = r.product_id WHERE return_id = ?i', $return['return_id']);
            foreach($return['items'] as &$item) {
                $item['product'] = fn_get_product_name($item['product_id']);
            }
        }
    }

    if (isset($params['return_id']) && !empty($params['return_id'])) {
        return reset($returns);
    }

    // get stats
    if (isset($params['get_stats']) && $params['get_stats']) {
        $user_ids = array_unique(array_column($returns, 'user_id'));
        list($time_from, $time_to) = fn_create_periods(['period'=> 'M']);
        foreach ($user_ids as $user_id) {

            $field = 'sum(o.total)';
            
            $condition = [];
            $join = '';
            $condition['is_parent'] = db_quote('parent_order_id = ?i', 0);
            $condition['user_id'] = db_quote('user_id = ?i', $user_id);
            
            if ($time_from) {
                $condition['time_from'] = db_quote('o.timestamp >= ?i', $time_from);
            }
            if ($time_to) {
                $condition['time_to'] = db_quote('o.timestamp <= ?i', $time_to);
            }
            if ($statuses = Registry::get('addons.promotion_progress.order_statuses')) {
                $condition['status'] = db_quote('o.status IN (?a)', array_keys($statuses));
            }

            $condition = implode(' AND ', $condition);

            $stats[$user_id]['orders_total'] = db_get_field("SELECT $field FROM ?:orders AS o $join WHERE $condition");

            $stats[$user_id]['requested'] = db_get_field('SELECT sum(total) FROM ?:returns WHERE timestamp >= ?i AND timestamp <= ?i AND user_id = ?i', $time_from, $time_to, $user_id) ?? 0;
            $stats[$user_id]['requested_percent'] = ($stats[$user_id]['orders_total']) ? round($stats[$user_id]['requested'] / $stats[$user_id]['orders_total'] * 100, 2) : 0;

            $stats[$user_id]['approved'] = db_get_field('SELECT sum(total) FROM ?:returns WHERE timestamp >= ?i AND timestamp <= ?i AND user_id = ?i AND status = ?s', $time_from, $time_to, $user_id, 'A') ?? 0;
            $stats[$user_id]['approved_percent'] = ($stats[$user_id]['orders_total']) ? round($stats[$user_id]['approved'] / $stats[$user_id]['orders_total'] * 100, 2) : 0;
        }

        foreach ($returns as &$return) {
            $return['user']['stats'] = $stats[$return['user_id']];
        }
        unset($return);
    }

    return [$returns, $params];
}

function fn_return_export_to_file($return_id) {
    $return = fn_get_returns(['return_id' => $return_id]);

    if ($return['status'] != ReturnOperationStatuses::APPROVED) {
        return 0;
    }

    $csv = $return['items'];
    array_walk($csv, function(&$v) { unset($v['return_id'], $v['product_id']); });

    $params['filename'] = $return['file_path'];
    $params['force_header'] = true;

    fn_mkdir(dirname(Registry::get('config.dir.files') . $return['file_path']));

    return fn_exim_put_csv($csv, $params, '"');
}

function fn_get_return_file($return_id) {
    $return = fn_get_returns(['return_id' => $return_id, 'get_items' => false]);

    fn_get_file(Registry::get('config.dir.files') . $return['file_path']);
}
