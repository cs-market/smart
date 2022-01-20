<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

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
        $return_data = [
            'user_id' => $auth['user_id'],
            'timestamp' => time(),
            'company_id' => $auth['company_id'],
            'comment' => '',
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
        $params['total_items'] = db_get_field("SELECT COUNT(DISTINCT(?:pages.page_id)) FROM ?:returns WHERE ?p", $condition);
        $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
    }

    $returns = db_get_array("SELECT * FROM ?:returns WHERE ?p", $condition);

    foreach ($returns as &$return) {
        $return['user'] = fn_get_user_short_info($return['user_id']);

        $user_code = is_numeric($return['user']['user_login']) ? $return['user']['user_login'] : $return['user']['email'];

        $return['file_path'] = $return['company_id'] . '/output/return.' . $user_code . '.#' . $return['return_id'] . '.csv';

        if (is_file(Registry::get('config.dir.files') . $return['file_path'])) {
            $return['file_exists'] = true;
        } else {
            $return['file_exists'] = false;
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

    return [$returns, $params];
}

function fn_return_export_to_file($return_id) {
    $return = fn_get_returns(['return_id' => $return_id]);

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
