<?php
/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*      Copyright (c) 2013 CS-Market Ltd. All rights reserved.             *
*                                                                         *
*  This is commercial software, only users who have purchased a valid     *
*  license and accept to the terms of the License Agreement can install   *
*  and use this program.                                                  *
*                                                                         *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*  PLEASE READ THE FULL TEXT OF THE SOFTWARE LICENSE AGREEMENT IN THE     *
*  "license agreement.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.  *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * **/

use Tygh\Registry;
use Tygh\Enum\Addons\OrderSplit\OrderSplitTypes;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_get_product_groups($params) {
    $condition = '';
    if (!empty($params['status'])) {
        $condition .= db_quote(' AND status = ?s', $params['status']);
    }

    if (Registry::get('runtime.company_id')) {
        $condition .= db_quote(" AND company_id = ?i", Registry::get('runtime.company_id'));
    }

    if (isset($params['group_id'])) {
        $condition .= db_quote(' AND group_id = ?i', $params['group_id']);
    }
    if (!empty($params['group_ids'])) {
        if (!is_array($params['group_ids'])) {
            $params['group_ids'] = explode(',', $params['group_ids']);
        }
        $condition .= db_quote(' AND group_id IN (?a)', $params['group_ids']);
    }

    $product_groups = db_get_hash_array("SELECT * FROM ?:product_groups WHERE 1 ?p", 'group_id', $condition);

    return $product_groups;
}

function fn_get_product_group_name($group_id)
{
    if (!empty($group_id)) {
        $group_name = db_get_field("SELECT ?:product_groups.group FROM ?:product_groups WHERE ?:product_groups.group_id = ?i", $group_id);
    }

    return !empty($group_name) ? $group_name : __('none');
}

function fn_update_product_groups($group_data, $group_id = 0) {
    if (!empty($group_id)) {
        db_query("UPDATE ?:product_groups SET ?u WHERE group_id = ?i", $group_data, $group_id);
    } else {
        $group_data['group_id'] = $group_id = db_query("INSERT INTO ?:product_groups ?e", $group_data);
    }
}

function fn_delete_product_group($group_id) {
    $result = db_query("DELETE FROM ?:product_groups WHERE group_id = ?i", $group_id);
    return $result;
}

function fn_product_groups_calculate_cart_taxes_pre(&$cart, $cart_products, $product_groups, $calculate_taxes, $auth) {
    foreach ($cart_products as $cart_id => $product) {
        if (isset($product['group_id'])) {
            $cart['products'][$cart_id]['group_id'] = $product['group_id'];
        }
    }
}

function fn_product_groups_pre_get_cart_product_data($hash, $product, $skip_promotion, $cart, $auth, $promotion_amount, &$fields, $join) {
    $fields[] = 'group_id';
}

function fn_product_groups_gather_additional_products_data_params($product_ids, $params, &$products, $auth, $products_images, $additional_images, $product_options, $has_product_options, $has_product_options_links) {
    $group_ids = fn_array_column($products, 'group_id');
    if ($group_ids) {
        $groups = fn_get_product_groups(array('group_ids' => $group_ids));
        foreach ($products as &$product) {
            $product['group'] = $groups[$product['group_id']];
        }
    }
}

function fn_product_groups_pre_update_order(&$cart, $order_id = 0) {
    $proto = $cart['product_groups'][0];
    unset($proto['products']);
    foreach ($cart['products'] as $cart_id => $product) {
        if (!isset($groups[$product['group_id']])) {
            $groups[$product['group_id']] = $proto;
            $groups[$product['group_id']]['group_id'] = $product['group_id'];
            $groups[$product['group_id']]['group'] = fn_get_product_groups(array('group_id' => $product['group_id']));
            $groups[$product['group_id']]['group'] = reset($groups[$product['group_id']]['group']);
            $groups[$product['group_id']]['subtotal'] = 0;
        }
        $groups[$product['group_id']]['products'][$cart_id] = $product;
        $groups[$product['group_id']]['name'] = $groups[$product['group_id']]['group']['group'];
        $groups[$product['group_id']]['subtotal'] += $product['price'] * $product['amount'];
    }
    $cart['product_groups'] = array_values($groups);
    if (count($cart['product_groups']) == 1) {
        $cart['group_id'] = reset($groups)['group']['group_id'] ? : 0;
    }
}

function fn_exim_import_product_group($group) {
    return db_get_field('SELECT `group_id` FROM ?:product_groups WHERE `group` = ?s', $group) ?? 0;
}

function fn_exim_get_product_group($group_id) {
    return db_get_field('SELECT `group` FROM ?:product_groups WHERE group_id = ?i', $group_id);
}

function fn_product_groups_check_min_amount($cart, &$check = true) {
    fn_product_groups_pre_update_order($cart);
    if (count($cart['product_groups']) == 1) {
        $product_group = reset($cart['product_groups']);
        if (isset($product_group['group']['min_order'])) {
            if ($product_group['group']['min_order'] > $product_group['subtotal']) {
                $formatter = Tygh::$app['formatter'];
                $min_amount = $formatter->asPrice($product_group['group']['min_order']);
                fn_set_notification(
                    'W',
                    __('notice'),
                    __('checkout.min_cart_subtotal_required', [
                        '[amount]' => $min_amount,
                        '[group]' => $product_group['group']['group'],
                    ])
                );
            }
            $check = false;
        }
    }
}

function fn_product_groups_calculate_cart_post(&$cart, $auth, $calculate_shipping, $calculate_taxes, $options_style, $apply_cart_promotions, $cart_products, $product_groups) {
    if (defined('API')) {
        fn_product_groups_pre_update_order($cart);
    }
}

function fn_product_groups_place_suborders_pre($order_id, $cart, $auth, $action, $issuer_id, &$suborder_cart, $key_group, $group) {
    $suborder_cart['group_id'] = $group['group_id'] ? : 0;
}

function fn_product_groups_place_suborders($cart, &$suborder_cart) {
    $group_id = $suborder_cart['group_id'];
    $products = $suborder_cart['products'];
    $suborder_cart['products'] = array_filter($products, function($product) use ($group_id) {
        return ($product['group_id'] == $group_id);
    });
    if ($products != $suborder_cart['products']) {
        unset($suborder_cart['promotions'], $suborder_cart['applied_promotions']);
    }
    if ($suborder_cart['subtotal'] == 0) {
        foreach ($suborder_cart['promotions'] as $promotion_id => $promo_data) {
            $found = false;
            foreach ($promo_data['bonuses'] as $bonus) {
                if ($bonus['bonus'] == 'free_products') {
                    $products = fn_array_column($bonus['value'], 'product_id');
                    $cart_products = array_column($suborder_cart['products'], 'product_id');
                    foreach ($products as $pid) {
                        $found = ($found || in_array($pid, $cart_products));
                    }
                }
            }
            if (!$found) {
                unset($suborder_cart['promotions'][$promotion_id]);
            }
        }
    }
}