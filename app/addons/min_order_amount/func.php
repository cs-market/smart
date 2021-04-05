<?php

use Tygh\Registry;
use Tygh\Models\Company;
use Tygh\Enum\ProductTracking;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_min_order_amount_get_companies($params, &$fields, $sortings, $condition, $join, $auth, $lang_code, $group) {
    $fields[] = 'min_order_amount';
}

function fn_min_order_amount_get_usergroups($params, $lang_code, &$field_list, $join, $condition, $group_by, $order_by, $limit) {
    $field_list .= ', a.min_order_amount';
}

function fn_min_order_amount_get_user_info($user_id, $get_profile, $profile_id, &$user_data) {
    if (!$user_data['min_order_amount'] && AREA == 'C') {
        $usergroups = array_filter($user_data['usergroups'], function($v) {
            return $v['status'] == 'A';
        });
        if (!empty($usergroups)) {
            $user_data['min_order_amount'] = db_get_field('SELECT max(min_order_amount) FROM ?:usergroups WHERE usergroup_id IN (?a)', array_keys($usergroups));
        }
    }
}

function fn_min_order_amount_get_users($params, &$fields, $sortings, $condition, $join, $auth) {
    if (isset($params['user_id'])) {
        $fields['min_order_amount'] = '?:users.min_order_amount';
    }
}

function fn_min_order_amount_get_users_post(&$users, $params, $auth) {
    if (isset($params['user_id'])) {
        foreach ($users as &$user) {

            if (!$user['min_order_amount']) {
                $usergroups = fn_get_user_usergroups($user['user_id']);
                $usergroups = array_filter($usergroups, function($v) {
                    return $v['status'] == 'A';
                });
                if (!empty($usergroups)) {
                    $user['min_order_amount'] = db_get_field('SELECT max(min_order_amount) FROM ?:usergroups WHERE usergroup_id IN (?a)', array_keys($usergroups));
                }
            }
        }
    }
}

// do all checks here
function fn_min_order_amount_calculate_cart_post(&$cart, $auth, $calculate_shipping, $calculate_taxes, $options_style, $apply_cart_promotions, $cart_products, $product_groups) {
    $cart['min_order_failed'] = false;
    unset($cart['min_order_notification']);
    $formatter = Tygh::$app['formatter'];
    list($orders) = fn_get_orders(['period' => 'D', 'user_id' => $cart['user_data']['user_id']]);

    if (!empty($cart['user_data']['min_order_amount'])) {
        if ($cart['total'] < $cart['user_data']['min_order_amount'] && empty($orders)) {
            $cart['min_order_failed'] = true;
            $min_amount = $formatter->asPrice($cart['user_data']['min_order_amount']);

            $cart['min_order_notification'] = __('text_min_products_amount_required') . ' ' . $min_amount;
        }
    } else {
        if (is_callable('fn_product_groups_split_cart')) {
            $p_groups = fn_product_groups_split_cart($cart);
            foreach ($p_groups as $product_group) {
                if (isset($product_group['group']['min_order'])) {
                    if (count($p_groups) > 1 && isset($product_group['group']) && $product_group['group']['group_id'] == '6') {
                        continue;
                    }
                    if (isset($product_group['group']) && $product_group['group']['min_order'] > $product_group['subtotal'] && !in_array($product_group['group_id'], array_column($orders, 'group_id'))) {
                        $cart['min_order_failed'] = true;
                        $min_amount = $formatter->asPrice($product_group['group']['min_order']);
                        $cart['min_order_notification'] = __('checkout.min_cart_subtotal_required', [
                            '[amount]' => $min_amount,
                            '[group]' => $product_group['group']['group'],
                        ]);
                    }
                }
            }
            // для аппетитпром в заказе один, игнорировать мин сумму по вендору, так как должна отработать только группа
            if ((count($p_groups) == 1 && isset(reset($p_groups)['group']) && reset($p_groups)['group']['group_id'] == '6')) return;
        }

        foreach ($cart['product_groups'] as $group) {
            $company_id = $group['company_id'];
            $group_orders = array_filter($orders, function($v) use ($company_id) {
                return $v['company_id'] == $company_id;
            });
            
            $min_order_amount = db_get_field('SELECT min_order_amount FROM ?:companies WHERE company_id = ?i', $company_id) - array_sum(array_column($group_orders, 'total'));

            if ($min_order_amount && $min_order_amount > $group['package_info']['C'] && $cart['total']) {
                $cart['min_order_failed'] = true;
                $min_amount = $formatter->asPrice($min_order_amount);

                $cart['min_order_notification'] = __('text_min_products_amount_required') . ' ' . $min_amount . ' ' . __('with_company') . ' ' . $group['name'];
            }
        }
    }
}
