<?php
/*****************************************************************************
*                                                                            *
*                   All rights reserved! eCom Labs LLC                       *
* http://www.ecom-labs.com/about-us/ecom-labs-modules-license-agreement.html *
*                                                                            *
*****************************************************************************/

if (!defined('BOOTSTRAP')) { die('Access denied'); }

use Tygh\Registry;

function fn_ecl_cross_selling_get_products($params, $fields, $sortings, &$condition, &$join, $sorting, $group_by, $lang_code, $having)
{
    if (!empty($params['related_products'])) {
        $no_product = ' AND products.product_id < -1';
        $product_id = Registry::get('runtime.product_id');

        if (!empty($product_id)) {
            $product_ids = fn_ecl_get_product_ids_for_block($product_id);

            $condition .= db_quote(' AND products.product_id IN (?n)', $product_ids);
        } else {
            $condition .= $no_product;
        }
    }

    return true;
}

function fn_ecl_related_products_update($product_id, $related_products = '')
{
    db_query('DELETE FROM ?:product_related_products WHERE product_id = ?i', $product_id);
    db_query('DELETE FROM ?:product_related_products WHERE related_id = ?i', $product_id);

    if (!empty($related_products)) {
        $related_products = explode(',', $related_products);

        $key = array_search($product_id, $related_products);

        if ($key !== false) {
            unset($related_products[$key]);
        }

        $data = array (
            'product_id' => $product_id
        );

        foreach ($related_products as $data['related_id']) {
            if (empty($data['related_id'])) {
                continue;
            }

            $reverse_data = array(
                'product_id' => $data['related_id'],
                'related_id' => $product_id
            );

            db_query('INSERT INTO ?:product_related_products ?e', $data);
            db_query('INSERT INTO ?:product_related_products ?e', $reverse_data);
        }
    }
}

function fn_ecl_get_related_products($product_id)
{
    if (empty($product_id)) {
        return array();
    }

    $related_products = db_get_fields('SELECT related_id FROM ?:product_related_products WHERE product_id = ?i', $product_id);

    return $related_products;
}

function fn_ecl_get_product_ids_for_block($product_id)
{
    if (empty($product_id)) {
        return array();
    }

    $related_products = fn_ecl_get_related_products($product_id);

    $_related_products = db_get_fields('SELECT product_id FROM ?:product_related_products WHERE related_id = ?i', $product_id);

    if (!empty($_related_products)) {
        $related_products = array_merge($related_products, $_related_products);
    }

    return $related_products;
}


function fn_ecl_cross_selling_post_add_to_cart($product_data, $cart, $auth, $update, $ids)
{
    if (empty($ids) || !is_array($ids) || Registry::get('addons.ecl_cross_selling.add_to_cart_notification') != 'Y') {
        return false;
    }

    $related_products = array();

    foreach ($ids as $product_id) {
        if (empty($product_id)) {
            continue;
        }

        $_related_products = fn_ecl_get_product_ids_for_block($product_id);

        if (!empty($_related_products) && is_array($_related_products)) {
            $related_products = array_merge($related_products, $_related_products);
        }
    }

    if (empty($related_products)) {
        return false;
    }

    $product_and_block_data = fn_ecl_additional_products_data_and_block_data($related_products);

    if (!empty($product_and_block_data['products']) && !empty($product_and_block_data['block_data'])) {
        Registry::get('view')->assign('related_products_for_cart', $product_and_block_data['products']);
        Registry::get('view')->assign('block_related_product_data', $product_and_block_data['block_data']);
    }
}

function fn_ecl_get_related_products_for_order($order_id)
{
    if (empty($order_id)) {
        return array();
    }

    $order_info = fn_get_order_info($order_id);
    
    if (empty($order_info['products']) || !is_array($order_info['products'])) {
        return array();
    }

    $related_products = array();

    foreach ($order_info['products'] as $product) {
        if (empty($product['product_id'])) {
            continue;
        }

        $_related_products = fn_ecl_get_product_ids_for_block($product['product_id']);

        if (!empty($_related_products) && is_array($_related_products)) {
            $related_products = array_merge($related_products, $_related_products);
        }
    }

    foreach ($related_products as $key => $related_product) {
        foreach ($order_info['products'] as $product) {
            if (empty($product['product_id'])) {
                continue;
            }

            if ($related_product == $product['product_id']) {
                unset($related_products[$key]);
            }
        }
    }

    $product_and_block_data = fn_ecl_additional_products_data_and_block_data($related_products);

    return $product_and_block_data;
}

function fn_ecl_additional_products_data_and_block_data($related_products)
{
    if (empty($related_products) || !is_array($related_products)) {
        return array();
    }

    $params = array(
        'item_ids' => implode(',', $related_products)
    );

    $limit = 0;
    $item_quantity = 3;

    if (Registry::get('runtime.controller') == 'checkout' && Registry::get('runtime.mode') == 'add') {
        $limit = Registry::get('addons.ecl_cross_selling.amount_product_add_to_cart');

        if ($item_quantity > $limit) {
            $item_quantity = $limit;
        }

    } elseif (Registry::get('runtime.controller') == 'checkout' && Registry::get('runtime.mode') == 'complete') {
        $limit = Registry::get('addons.ecl_cross_selling.amount_product_reminder');

        $item_quantity = 5;

        if ($item_quantity > $limit) {
            $item_quantity = $limit;
        }
    } elseif (Registry::get('runtime.controller') == 'checkout' && Registry::get('runtime.mode') == 'cart') {
        $limit = Registry::get('addons.ecl_cross_selling.amount_product_cart');

        $item_quantity = 3;

        if ($item_quantity > $limit) {
            $item_quantity = $limit;
        }
    }

    list($products, ) = fn_get_products($params, $limit);

    if (empty($products) || !is_array($products)) {
        return array();
    }

    $count_product = count($products);

    if ($count_product < $item_quantity) {
        $item_quantity = $count_product;
    }

    $product_params = array(
        'get_icon' => 1,
        'get_detailed' => 1,
        'get_options' => 1
    );

    fn_gather_additional_products_data($products, $product_params);

    $block_data = array(
        'block_id' => 'related_product_id',
        'properties' => array(
            'item_quantity' => $item_quantity,
            'outside_navigation' => 'Y',
            'not_scroll_automatically' => 'Y',
            'hide_add_to_cart_button' => 'Y'
        ),
    );

    $product_and_block_data = array(
        'products' => $products,
        'block_data' => $block_data
    );

    return $product_and_block_data;
}

function fn_ecl_get_related_products_for_cart($cart)
{
    if (empty($cart['products']) || !is_array($cart['products'])) {
        return false;
    }

    $related_products = array();

    foreach ($cart['products'] as $cart_key => $product) {
        if (empty($product['product_id'])) {
            continue;
        }

        $_related_products = fn_ecl_get_product_ids_for_block($product['product_id']);

        if (empty($_related_products) || !is_array($_related_products)) {
            continue;
        }

        $product_and_block_data = fn_ecl_additional_products_data_and_block_data($_related_products);

        if (empty($product_and_block_data['products']) || !is_array($product_and_block_data['products'])) {
            continue;
        }

        $product_and_block_data['block_data']['block_id'] = 'related_product_block_id' . $cart_key;

        foreach ($product_and_block_data['products'] as $key => &$related_product) {
            foreach ($cart['products'] as $_product) {
                if (empty($_product['product_id'])) {
                    continue;
                }

                if ($related_product['product_id'] == $_product['product_id']) {
                    unset($product_and_block_data['products'][$key]);
                }
            }
        }

        $related_products[$cart_key] = $product_and_block_data;
    }

    return $related_products;
}
