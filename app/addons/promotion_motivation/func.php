<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_promotion_motivation_promotion_apply_pre($promotions, $zone, &$data, $auth, $cart_products) {
    if ($zone == 'cart') {
        $formatter = Tygh::$app['formatter'];
        $data['promotion_motivation'] = [];
        foreach ($promotions[$zone] as $promotion) {
            // Rule is valid and can be applied
            if ($zone == 'cart') {
                $data['has_coupons'] = empty($data['has_coupons']) ? fn_promotion_has_coupon_condition($promotion['conditions']) : $data['has_coupons'];
            }
            if (!fn_check_promotion_conditions($promotion, $data, $auth, $cart_products)) {
                foreach (['subtotal'] as $progress) {

                    if ($motivation_condition = fn_find_promotion_condition($promotion['conditions'], $progress, true)) {
                        // check promotion wo progress
                        if (fn_check_promotion_conditions($promotion, $data, $auth, $cart_products)) {
                            $current_value = fn_promotion_get_current_value($promotion['promotion_id'], $motivation_condition, $data, $auth, $cart_products);

                            $motivation_type = 'motivation_' . $progress . '_' . $motivation_condition['operator'];
                            $bonus = reset($promotion['bonuses']);
                            if ($bonus['bonus'] == 'free_products') {
                                $products = [];
                                $product_ids = array_column($bonus['value'], 'product_id');
                                foreach($product_ids as $product_id) {
                                    $products[] = fn_get_product_name($product_id);
                                }
                                $products = implode(',', $products);
                            }
                            
                            $motivation_replacement = [
                                '[current_value]' => $formatter->asPrice($current_value),
                                '[value]' => $formatter->asPrice($motivation_condition['value']),
                                '[diff]' => $formatter->asPrice(abs($motivation_condition['value'] - $current_value)),
                                '[gift]' => $products
                            ];
                            $data['promotion_motivation'] = ['title' => $promotion['name'], 'body' => __($motivation_type,  $motivation_replacement)];
                        }
                    }
                }
            }
        }
    }
}

if (!is_callable('fn_find_promotion_condition')) {
    function fn_find_promotion_condition(&$conditions_group, $needle, $remove = false) {
        foreach ($conditions_group['conditions'] as $i => $group_item) {
            if (isset($group_item['conditions'])) {
                return fn_find_promotion_condition($conditions_group['conditions'][$i], $needle, $remove);
            } elseif ((is_array($needle) && in_array($group_item['condition'], $needle)) || $group_item['condition'] == $needle) {
                if ($remove) unset($conditions_group['conditions'][$i]);
                return $group_item;
            }
        }

        return false;
    }    
}

function fn_promotion_get_current_value($promotion_id, $promotion, $data, $auth, $cart_products)
{
    static $parent_orders = array();
    $stop_validating = false;
    $result = true;
    $schema = fn_promotion_get_schema('conditions');

    fn_set_hook('pre_promotion_validate', $promotion_id, $promotion, $data, $stop_validating, $result, $auth, $cart_products);

    if ($stop_validating) {
        return $result;
    }

    if (empty($promotion['condition'])) { // if promotion is unconditional, apply it
        return true;
    }

    if (empty($schema[$promotion['condition']])) {
        return false;
    }

    $promotion['value'] = !isset($promotion['value']) ? '' : $promotion['value'];
    $value = '';

    if (!empty($data['parent_order_id'])) {
        $parent_order_id = $data['parent_order_id'];

        if (!isset($parent_orders[$parent_order_id])) {
            $parent_orders[$parent_order_id] = array(
                'cart' => array(
                    'order_id' => $parent_order_id
                ),
                'cart_products' => array(),
                'product_groups' => array(),
            );

            fn_form_cart($parent_order_id, $parent_orders[$parent_order_id]['cart'], $auth);
            list (
                $parent_orders[$parent_order_id]['cart_products'],
                $parent_orders[$parent_order_id]['product_groups']
            ) = fn_calculate_cart_content($parent_orders[$parent_order_id]['cart'], $auth);
        }

        if (isset($parent_orders[$parent_order_id])) {
            $data = $parent_orders[$parent_order_id]['cart'];
            $cart_products = $parent_orders[$parent_order_id]['cart_products'];
        }
    }

    // Ordinary field
    if (!empty($schema[$promotion['condition']]['field'])) {
        // Array definition, parse it
        if (strpos($schema[$promotion['condition']]['field'], '@') === 0) {
            $value = fn_promotion_get_object_value($schema[$promotion['condition']]['field'], $data, $auth, $cart_products);
        } else {
            // If field can be used in both zones, it means that we're using products
            if (in_array('catalog', $schema[$promotion['condition']]['zones']) && in_array('cart', $schema[$promotion['condition']]['zones']) && !empty($cart_products)) {// this is the "cart" zone. FIXME!!!
                foreach ($cart_products as $v) {
                    if ($promotion['operator'] == 'nin') {
                        if (fn_promotion_validate_attribute($v[$schema[$promotion['condition']]['field']], $promotion['value'], 'in')) {
                            return false;
                        }
                    } else {
                        if (fn_promotion_validate_attribute($v[$schema[$promotion['condition']]['field']], $promotion['value'], $promotion['operator'])) {
                            return true;
                        }
                    }
                }

                return $promotion['operator'] == 'nin' ? true : false;
            }

            if (!isset($data[$schema[$promotion['condition']]['field']])) {
                return false;
            }

            $value = $data[$schema[$promotion['condition']]['field']];
        }
        // Field is the result of function
    } elseif (!empty($schema[$promotion['condition']]['field_function'])) {
        $function_args = $schema[$promotion['condition']]['field_function'];
        $function_name = array_shift($function_args);
        $function_args_definitions = $function_args;

        // If field can be used in both zones, it means that we're using products
        if (
            in_array('catalog', $schema[$promotion['condition']]['zones'])
            && in_array('cart', $schema[$promotion['condition']]['zones'])
            && !empty($cart_products)
        ) { // this is the "cart" zone. FIXME!!!
            foreach ($cart_products as $product) {
                $function_args = $function_args_definitions;
                foreach ($function_args as $k => $v) {
                    if (strpos($v, '@') !== false) {
                        $function_args[$k] = & fn_promotion_get_object_value($v, $product, $auth, $cart_products);
                    } elseif ($v == '#this') {
                        $function_args[$k] = & $promotion;
                    } elseif ($v == '#id') {
                        $function_args[$k] = & $promotion_id;
                    }
                }

                $value = call_user_func_array($function_name, $function_args);

                if ($promotion['operator'] == 'nin') {
                    if (fn_promotion_validate_attribute($value, $promotion['value'], 'in')) {
                        return false;
                    }
                } else {
                    if (fn_promotion_validate_attribute($value, $promotion['value'], $promotion['operator'])) {
                        return true;
                    }
                }
            }

            return $promotion['operator'] == 'nin' ? true : false;
        }

        foreach ($function_args as $k => $v) {
            if (strpos($v, '@') !== false) {
                $function_args[$k] = & fn_promotion_get_object_value($v, $data, $auth, $cart_products);
            } elseif ($v == '#this') {
                $function_args[$k] = & $promotion;
            } elseif ($v == '#id') {
                $function_args[$k] = & $promotion_id;
            }
        }

        $value = call_user_func_array($function_name, $function_args);
    }

    return $value;
}
