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

function fn_product_groups_calculate_cart_items(&$cart, $cart_products, $auth, $apply_cart_promotions) {
    foreach ($cart_products as $cart_id => $product) {
        if (isset($product['group_id'])) {
            $cart['products'][$cart_id]['group_id'] = $product['group_id'];
        }
    }

    if ($cart['recalculate'] == true) {
        $group_ids = array_unique(fn_array_column($cart_products, 'group_id'));
        $cart['groups'] = fn_get_product_groups(array('group_ids' => $group_ids));
    }
}

function fn_product_groups_pre_get_cart_product_data($hash, $product, $skip_promotion, $cart, $auth, $promotion_amount, &$fields, $join) {
    $fields[] = 'group_id';
}

// return product_groups
function fn_product_groups_split_cart($cart) {
    $p_groups = array();

    if (!fn_cart_is_empty($cart)) {
        if (!empty($cart['groups'])) {
            $group_ids = array_unique(fn_array_column($cart['products'], 'group_id'));
            $cart['groups'] = fn_get_product_groups(array('group_ids' => $group_ids));
        }

        // if (count($cart['groups']) > 1) {
            $proto = reset($cart['product_groups']);
            unset($proto['products']);
            foreach ($cart['products'] as $cart_id => $product) {
                $group_id = $product['group_id'];
                if (!isset($p_groups[$group_id])) {
                    $p_groups[$group_id] = $proto;
                    $p_groups[$group_id]['group_id'] = $group_id;
                    if ($group_id) {
                        $p_groups[$group_id]['group'] = $cart['groups'][$product['group_id']];
                        $p_groups[$group_id]['name'] = $p_groups[$group_id]['group']['group'];
                    }
                    $p_groups[$group_id]['subtotal'] = 0;
                }
                $p_groups[$group_id]['products'][$cart_id] = $product;
                $p_groups[$group_id]['subtotal'] += $product['price'] * $product['amount'];
            }
        // }
    }

    return !empty($p_groups) ? array_values($p_groups) : $cart['product_groups'];
}

function fn_product_groups_pre_update_order(&$cart, $order_id = 0) {
    $cart['product_groups'] = fn_product_groups_split_cart($cart);
    if (count($cart['product_groups']) == 1) {
        $cart['group_id'] = reset($cart['product_groups'])['group_id'] ? : 0;
    }
}

function fn_exim_import_product_group($group) {
    return db_get_field('SELECT `group_id` FROM ?:product_groups WHERE `group` = ?s', $group) ?? 0;
}

function fn_exim_get_product_group($group_id) {
    return db_get_field('SELECT `group` FROM ?:product_groups WHERE group_id = ?i', $group_id);
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

function fn_product_groups_get_product_fields(&$fields) {
    $fields[] = array('name' => '[data][group_id]', 'text' => __('product_group'));
}

function fn_product_groups_pre_get_orders($params, &$fields, $sortings, $get_totals, $lang_code) {
    $fields[] = '?:orders.group_id';
}
