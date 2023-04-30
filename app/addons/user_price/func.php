<?php

use Tygh\Enum\SiteArea;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

//  [HOOKs]
function fn_user_price_update_product_post($product_data, $product_id, $lang_code, $create)
{
    if (isset($product_data['user_price'])) {
        fn_update_product_user_price($product_id, $product_data['user_price'], false);
    }
}

function fn_user_price_get_product_data_post(&$product_data, $auth, $preview, $lang_code)
{
    if (SiteArea::isStorefront(AREA)) {
        $product_data['user_price'] = fn_get_product_user_price($product_data['product_id']);
    }
}

function fn_user_price_get_products_post(&$products, $params, $lang_code)
{
    if (SiteArea::isStorefront(AREA)) {
        $product_ids = array_keys($products);

        if (!$product_ids) {
            return true;
        }

        $user_prices = fn_get_product_user_price($product_ids);

        foreach ($user_prices as $user_price) {
            $products[$user_price['product_id']]['user_price'][] = $user_price;
        }
    }
}

function fn_user_price_gather_additional_product_data_before_discounts(&$product, $auth, $params)
{
    if (isset($product['user_price']) && !empty($product['user_price']) && AREA == 'C') {
        $product['price'] = $product['user_price'][0]['price'];
        $product['base_price'] = $product['price'];
        unset($product['promotions']);
    }
}

function fn_user_price_get_order_items_info_post(&$order, $v, $k)
{
    $user_prices = fn_get_product_user_price($v['product_id'], $order['user_id']);
    if (!empty($user_prices)) {
        $order['products'][$k]['original_price'] = $user_prices[0]['price'];
    }
}

function fn_user_price_storages_get_cart_product_data($product_id, &$_pdata, $product, $auth, $cart, $hash) {
    list($user_prices) = fn_get_product_user_price_with_params([
        'product_id' => $product_id,
        'user_ids' => $auth['user_id'],
        'product' => $product
    ]);
    if (!empty($user_prices)) {
        $_pdata['price'] = $user_prices[0]['price'];
    }
}

function fn_user_price_get_product_price_post($product_id, $amount, $auth, &$price) {
    $user_prices = fn_get_product_user_price($product_id);
    if (!empty($user_prices)) {
        $price = $user_prices[0]['price'];
    }
}
//  [/HOOKs]

function fn_update_product_user_price($product_id, $user_prices, $delete_price = true)
{
    array_walk($user_prices, function(&$value, $k) use ($product_id) {$value['product_id'] = $product_id;});

    $delete_condition = [
        db_quote('product_id = ?i', $product_id)
    ];
    if (!$delete_price) {
        if ($delete_prices = array_filter($user_prices, function($v) {return (!empty($v['user_id']) && empty($v['price']));} )) {
            $delete_condition[] = db_quote('user_id IN (?a)', array_column($delete_prices, 'user_id'));
            $delete_price = true;
        }
    }
    $user_prices = array_filter($user_prices, function($v) {return (isset($v['user_id']) && is_numeric($v['user_id']));} );

    if ($delete_price) {
        db_query("DELETE FROM ?:user_price WHERE ?p", implode(' AND ', $delete_condition));
    }

    $user_prices = array_filter($user_prices, function($v) {
        return (isset($v['price']) && is_numeric($v['price']));
    });

    if (!empty($user_prices)) {
        db_query('REPLACE INTO ?:user_price ?m', $user_prices);
    }
    return true;
}

function fn_get_product_user_price($product_id, $user_id = 0)
{
    // backward compability
    list($user_price) = fn_get_product_user_price_with_params([
        'product_id' => $product_id,
        'user_ids' => $user_id ?: []
    ]);

    return $user_price;
}

function fn_get_product_user_price_with_params($params = [])
{
    $default_params = [
        'product_id' => 0,
        'pname' => '',
        'user_ids' => [],
        'limit' => 0,
        'page' => 1,
        'items_per_page' => 0
    ];

    $params = array_merge($default_params, $params);

    $condition = '';
    $join = '';

    $product_id = is_array($params['product_id']) ? $params['product_id'] : (array)$params['product_id'];
    $condition .= db_quote(" AND p.product_id IN (?n)", $product_id);

    if ($params['pname']) {
        $pname = '%' . $params['pname'] . '%';
        $join .= db_quote(" LEFT JOIN ?:users as u ON u.user_id = p.user_id");
        $condition .= db_quote(" AND ("
            . " u.user_login LIKE ?l"
            . " OR u.email LIKE ?l"
            . " OR u.firstname LIKE ?l"
            . " OR u.lastname LIKE ?l"
        . ")", $pname, $pname, $pname, $pname);
    }

    //  only for current user
    if (SiteArea::isStorefront(AREA)) {
        if (!empty(Tygh::$app['session']['auth']['user_id'])) {
            $condition .= db_quote(" AND p.user_id = ?i", Tygh::$app['session']['auth']['user_id']);
        } else {
            //  only for signed users
            return [null, []];
        }
    } else {
        if ($params['user_ids']) {
            $condition .= db_quote(" AND p.user_id IN (?n)", $params['user_ids']);
        }
    }

    $limit = '';
    if (!empty($params['limit'])) {
        $limit = db_quote(" LIMIT 0, ?i", $params['limit']);
    } elseif (!empty($params['items_per_page'])) {
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $calc_found_rows = 'SQL_CALC_FOUND_ROWS';

    fn_set_hook('get_user_price', $params, $join, $condition);

    $user_prices = db_get_array("SELECT $calc_found_rows p.* FROM ?:user_price as p $join WHERE 1 $condition $limit");

    $params['total_items'] = empty($params['items_per_page'])
        ? count($user_prices)
        : db_get_found_rows();

    //  info for settings
    if (AREA == 'A') {
        fn_get_user_price_user_data($user_prices);
    }

    return [$user_prices, $params];
}

function fn_get_user_price_user_data(&$user_prices)
{
    $user_ids = fn_array_column($user_prices, 'user_id');
    $user_datas = db_get_hash_array("SELECT user_id, firstname, lastname, email FROM ?:users WHERE user_id IN (?n)", 'user_id', $user_ids);

    array_walk($user_prices, function(&$user_price) use ($user_datas) {
        $user_price['user_data'] = $user_datas[$user_price['user_id']] ?? '';
    });
}

function fn_user_price_delete_product_post($product_id, $product_deleted) {
    if ($product_deleted) db_query('DELETE FROM ?:user_price WHERE product_id = ?i', $product_id);
}

function fn_user_price_post_delete_user($user_id, $user_data, $result) {
    if ($result) db_query('DELETE FROM ?:user_price WHERE user_id = ?i', $user_id);
}
