<?php

use Tygh\Enum\ProductTracking;
use Tygh\Registry;
use Tygh\Enum\OutOfStockActions;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_promotion_step_check_amount_in_stock_before_check($product_id, $amount, $product_options, $cart_id, $is_edp, $original_amount, $cart, $update_id, &$product, $current_amount){
    foreach ($cart['products'] as $key => $products) {
        if(isset($products['extra']['bonus']) && $products['product'] == $product['product'] && $products['extra']['bonus'] == 'apply_bonus'){
            $product['min_qty'] = $product['extra']['amount_bonus'];
        }
    }
}

function fn_promotion_step_calculate_cart_post($cart, $auth, $calculate_shipping, $calculate_taxes, $options_style, $apply_cart_promotions, &$cart_products, $product_groups) {
    foreach ($cart['products'] as $key => &$products) {
        if(isset($products['extra']['bonus']) && $products['extra']['bonus'] == 'apply_bonus'&&$products['amount']!=$products['extra']['amount_bonus']*$products['extra']['amount_step']){
            $condition_amount = $products['extra']['amount_bonus']*$products['extra']['amount_step'];
            $cart_products[$key]['amount'] = $condition_amount;
            $products['amount'] = $condition_amount;
            $need_recalculate = true;
        }
    }
}

function fn_promotion_step_get_products_amount($promotion_id, $cart, $cart_products, $type = 'S', $calculate_by_packs = false) {
    $promotion =  fn_get_promotion_data($promotion_id);
    $amount = 0;
    foreach ($promotion['conditions']['conditions'] as $key => $conditions) {
        if(isset($conditions['condition']) && $conditions['condition'] == 'products'){
            foreach ($conditions['value'] as $key => $value) {
                foreach ($cart_products as $k => $v) {
                    if ($type == 'S') {
                        if (fn_exclude_from_shipping_calculate($cart['products'][$k])) {
                            continue;
                        }
                    }elseif ($type == 'C') {
                        if (isset($v['exclude_from_calculate'])) {
                            continue;
                        }
                    }
                    if($value['product_id'] == $v['product_id']){
                        if (!$calculate_by_packs) {
                            $amount += $v['amount'];
                        } else {
                            $items_in_package = $v['items_in_package'] ?? $v['qty_step'];
                            $amount += floor($v['amount']/$items_in_package);
                        }
                    }
                }
            }
        } elseif (isset($conditions['condition']) && $conditions['condition'] == 'categories') {
            foreach ($cart_products as $k => $v) {
                if ($type == 'S') {
                    if (fn_exclude_from_shipping_calculate($cart['products'][$k])) {
                        continue;
                    }
                } elseif ($type == 'C') {
                    if (isset($v['exclude_from_calculate'])) {
                        continue;
                    }
                }
                if(in_array($promotion['condition_categories'], $v['category_ids'])){
                    if (!$calculate_by_packs) {
                        $amount += $v['amount'];
                    } else {
                        $items_in_package = $v['items_in_package'] ?? $v['qty_step'];
                        $amount += floor($v['amount']/$items_in_package);
                    }
                }
            }
        }
    }

    return $amount;
}

function fn_promotion_step_unconditional_true() {
    return true;
}

function fn_promotion_step_apply_cart_rule($bonus, &$cart, &$auth, &$cart_products) {
    $promotion =  fn_get_promotion_data($bonus['promotion_id']);
    if ($bonus['bonus'] == 'promotion_step_free_products') {

        $conditions = $promotion['conditions'];
        fn_cleanup_promotion_condition($conditions, ['promotion_step']);
        $package_step = empty($conditions['conditions']) ? 1 : 0;
        
        $amount = fn_promotion_step_get_products_amount($bonus['promotion_id'], $cart, $cart_products, $type = 'S', $package_step);

        foreach ($bonus['value'] as $p_data) {

            $product_data = array (
                $p_data['product_id'] => array (
                    'amount' => $p_data['amount'],
                    'product_id' => $p_data['product_id'],
                    'extra' => array (
                        'exclude_from_calculate' => true,
                        'aoc' => empty($p_data['product_options']),
                        'saved_options_key' => $bonus['promotion_id'] . '_' . $p_data['product_id'],
                    )
                ),
            );

            if (!empty($cart['saved_product_options'][$bonus['promotion_id'] . '_' . $p_data['product_id']])) {
                $product_data[$p_data['product_id']]['product_options'] = $cart['saved_product_options'][$bonus['promotion_id'] . '_' . $p_data['product_id']];
            } elseif (!empty($p_data['product_options'])) {
                $product_data[$p_data['product_id']]['product_options'] = $p_data['product_options'];
            }

            // Restore object_id if needed
            if (!empty($cart['saved_object_ids'][$bonus['promotion_id'] . '_' . $p_data['product_id']])) {
                $product_data[$p_data['product_id']]['saved_object_id'] = $cart['saved_object_ids'][$bonus['promotion_id'] . '_' . $p_data['product_id']];
            }

            $existing_products = array_keys($cart['products']);

            if (
                !fn_allowed_for('ULTIMATE')
                || (
                    fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')
                    && (
                        fn_check_company_id('products', 'product_id', $p_data['product_id'], Registry::get('runtime.company_id'))
                        || fn_ult_is_shared_product($p_data['product_id'], Registry::get('runtime.company_id')) == 'Y'
                    )
                )
            ) {
                $promotion_step_limit = fn_find_promotion_condition($promotion['conditions'], 'promotion_step_limit');

                foreach ($promotion['conditions']['conditions'] as $key => $condition) {
                    if($condition['operator'] == 'gte' && in_array($condition['condition'], ['promotion_step', 'promotion_package_step']) && $condition['value']){
                        $step = floor($amount/$condition['value']);
                        if ($promotion_step_limit && $step > $promotion_step_limit['value']) $step = $promotion_step_limit['value'];

                        if ($step) {
                            $product_data = array (
                                $p_data['product_id'] => array (
                                    'amount' => $step,
                                    'product_id' => $p_data['product_id'],
                                    'extra' => array (
                                        'bonus' => 'apply_bonus',
                                        'amount_step' => $step,
                                        'amount_bonus' => $p_data['amount'],
                                        'exclude_from_calculate' => true,
                                        'bonus' => 'apply_bonus',
                                        'aoc' => empty($p_data['product_options']),
                                        'saved_options_key' => $bonus['promotion_id'] . '_' . $p_data['product_id'],
                                    )
                                ),
                            );
                            $ids = fn_add_product_to_cart($product_data, $cart, $auth);

                            $new_products = array_diff(array_keys($cart['products']), $existing_products);
                            if (!empty($new_products)) {
                                $hash = array_pop($new_products);
                            } else {
                                $hash = key($ids);
                            }

                            $_cproduct = fn_get_cart_product_data($hash, $cart['products'][$hash], true, $cart, $auth, !empty($new_products) ? 0 : $p_data['amount']);
                            if (!empty($_cproduct)) {
                                $cart_products[$hash] = $_cproduct;
                            }
                        }
                    }
                }
            }
        }
    }

    if ($bonus['bonus'] == 'promotion_step_give_condition_products') {
        if ($step = fn_find_promotion_condition($promotion['conditions'], 'promotion_step')) {
            $step_condition = 'promotion_step';
        } elseif ($step = fn_find_promotion_condition($promotion['conditions'], 'promotion_package_step')) {
            $step_condition = 'promotion_package_step';
        }

        $condition_products = fn_find_promotion_condition($promotion['conditions'], 'products');
        if ($condition_products) {
            $condition_products = array_column($condition_products['value'], 'product_id');
        } else {
            // process category conditions here
        }

        foreach ($cart_products as $cart_product) {
            if (in_array($cart_product['product_id'], $condition_products)) {
                if ($step_condition == 'promotion_package_step') {
                    $amount = $cart_product['amount']/$cart_product['items_in_package'];
                } else {
                    $amount = $cart_product['amount'];
                }

                $execution_count = floor($amount/$step['value']);
                $promotion_step_limit = fn_find_promotion_condition($promotion['conditions'], 'promotion_step_limit');
                if ($promotion_step_limit && $execution_count > $promotion_step_limit['value']) $execution_count = $promotion_step_limit['value'];

                if ($execution_count) {
                    $product_data = array (
                        $cart_product['product_id'] => array (
                            'amount' => $bonus['value'] * $execution_count,
                            'product_id' => $cart_product['product_id'],
                            'extra' => array (
                                'bonus' => 'apply_bonus',
                                'amount_step' => $execution_count,
                                'amount_bonus' => $bonus['value'],
                                'exclude_from_calculate' => true,
                                'bonus' => 'apply_bonus',
                                'aoc' => empty($p_data['product_options']),
                                'saved_options_key' => $bonus['promotion_id'] . '_' . $cart_product['product_id'],
                            )
                        ),
                    );
                    $ids = fn_add_product_to_cart($product_data, $cart, $auth);

                    $new_products = array_diff(array_keys($cart['products']), $existing_products);
                    if (!empty($new_products)) {
                        $hash = array_pop($new_products);
                    } else {
                        $hash = key($ids);
                    }

                    $_cproduct = fn_get_cart_product_data($hash, $cart['products'][$hash], true, $cart, $auth, !empty($new_products) ? 0 : $p_data['amount']);
                    if (!empty($_cproduct)) {
                        $cart_products[$hash] = $_cproduct;
                    }
                }
            }
        }
    }

    return true;
}
