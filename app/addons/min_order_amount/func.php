<?php

use Tygh\Registry;
use Tygh\Enum\YesNo;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

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

function fn_min_order_amount_pre_place_order(&$cart, $allow, $product_groups) {
    if ($allow) {
        $cart['skip_min_check'] = true;
    }
}

// do all checks here
function fn_min_order_amount_calculate_cart_post(&$cart, $auth, $calculate_shipping, $calculate_taxes, $options_style, $apply_cart_promotions, $cart_products, $product_groups) {
    $cart['min_order_failed'] = false;
    unset($cart['min_order_notification']);
    if (isset($cart['skip_min_check']) && $cart['skip_min_check']) {
        return;
    }

    $formatter = Tygh::$app['formatter'];
    $orders = array();

    $additional_ordering_checked = false;
    $additional_ordering_passed = true;
    foreach ($cart['product_groups'] as $group_key => $group) {
        if ($group['company_id'] && YesNo::toBool(db_get_field('SELECT allow_additional_ordering FROM ?:companies WHERE company_id = ?i', $group['company_id']))) {
            $additional_ordering_checked = true;
            $params = [
                'user_id' => $auth['user_id'],
                'company_id' => $group['company_id']
            ];
            if (!empty($cart['profile_id'])) {
                $params['profile_id'] = $cart['profile_id'];
            }
            if (!empty($group['storage_id'])) {
                $params['storage_id'] = $group['storage_id'];
            }
            $params['delivery_date'] = (isset($group['delivery_date'])) ? fn_parse_date($group['delivery_date']) : 0;

            list($orders) = fn_get_orders($params);
            $additional_ordering_passed = $additional_ordering_passed && !empty($orders);
        } else {
            $additional_ordering_passed = false;
            break;
        }
    }

    if ($additional_ordering_passed && $additional_ordering_checked) return;

    if (!empty($cart['user_data']['min_order_amount'])) {
        if ($cart['total'] < $cart['user_data']['min_order_amount'] && empty($orders)) {
            $cart['min_order_failed'] = true;
            $min_amount = $formatter->asPrice($cart['user_data']['min_order_amount']);

            $cart['min_order_notification'] = __('text_min_products_amount_required') . ' ' . $min_amount;
        }
    } else {
        $check_weight = true;
        if (is_callable('fn_product_groups_split_cart')) {
            $p_groups = fn_product_groups_split_cart($cart, true);
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

                if (!empty($product_group['group']['min_order_weight']) && $product_group['group']['min_order_weight'] > $product_group['package_info']['W']) {
                    $cart['min_order_failed'] = true;
                    $cart['min_order_notification'] = __('text_min_products_weight_required') . ' ' . $product_group['group']['min_order_weight'] . ' ' . Registry::get('settings.General.weight_symbol');
                }
                // если корзина разбивается по группам, то проверяем группы и не проверяем общие правила вендора
                if (!empty($product_group['group'])) {
                    $check_weight = false;
                }
            }
            // для аппетитпром в заказе один, игнорировать мин сумму по вендору, так как должна отработать только группа
            if ((count($p_groups) == 1 && isset(reset($p_groups)['group']) && reset($p_groups)['group']['group_id'] == '6')) return;
        }

        foreach ($cart['product_groups'] as $group) {
            $company_id = $group['company_id'];
            $group_orders = array_filter($orders, function($v) use ($company_id) {
                return ($v['company_id'] == $company_id);
            });

            $mins = db_get_row('SELECT min_order_amount, min_order_weight FROM ?:companies WHERE company_id = ?i', $company_id);

            if ($mins['min_order_amount'] && $mins['min_order_amount'] > $group['package_info']['C'] && $cart['total'] && empty($group_orders)) {
                $cart['min_order_failed'] = true;
                $min_amount = $formatter->asPrice($mins['min_order_amount']);
                $cart['min_order_notification'] = __('text_min_products_amount_required') . ' ' . $min_amount . ' ' . __('with_company') . ' ' . $group['name'];
            }

            if ($check_weight && $mins['min_order_weight'] && $mins['min_order_weight'] > $group['package_info']['W']) {
                $cart['min_order_failed'] = true;
                $cart['min_order_notification'] = __('text_min_products_weight_required') . ' ' . $mins['min_order_weight'] . ' ' . Registry::get('settings.General.weight_symbol');
            }

            if (!empty($group['storage_id'])) {
                if ($mins = Registry::get('runtime.storages.'.$group['storage_id'])) {
                    if ($mins['min_order_amount'] && $mins['min_order_amount'] > $group['package_info']['C'] && $cart['total'] && empty($group_orders)) {
                        $cart['min_order_failed'] = true;
                        $min_amount = $formatter->asPrice($mins['min_order_amount']);
                        $cart['min_order_notification'] = __('text_min_products_amount_required') . ' ' . $min_amount . ' ' . __('storages.with_storage') . ' <b>' . $group['name'] . '</b>';
                    }

                    if ($check_weight && $mins['min_order_weight'] && $mins['min_order_weight'] > $group['package_info']['W']) {
                        $cart['min_order_failed'] = true;
                        $cart['min_order_notification'] = __('text_min_products_weight_required') . ' ' . $mins['min_order_weight'] . ' ' . Registry::get('settings.General.weight_symbol') . ' ' . __('storages.with_storage') . ' <b>' . $group['name'] . '</b>';
                    }
                }
            }
        }

        fn_set_hook('min_order_amount_extra_check',$product_groups, $cart, $cart_products);
    }
}

function fn_min_order_amount_allow_place_order_post($cart, $auth, $parent_order_id, $total, &$result) {
    if ($cart['min_order_failed']) {
        $result = false;
    }
}

function fn_min_order_amount_get_usergroups($params, $lang_code, &$field_list, $join, $condition, $group_by, $order_by, $limit) {
    $field_list .= ', a.min_order_amount';
}
