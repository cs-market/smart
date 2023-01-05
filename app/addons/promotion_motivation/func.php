<?php

use Tygh\Registry;
use Tygh\Enum\SiteArea;

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
                                '[gift]' => $products ?? ''
                            ];
                            $data['promotion_motivation'] = ['title' => $promotion['name'], 'body' => __($motivation_type,  $motivation_replacement)];
                        }
                    }
                }
            }
        }
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

function fn_promotion_motivation_get_promotions($params, &$fields, $sortings, &$condition, $join, $group, $lang_code) {
    if (Registry::get('addons.category_promotion.status') == 'A') {
        if (isset($params['product_or_bonus_product'])) {
            $category_ids = db_get_fields('SELECT category_id FROM ?:products_categories WHERE product_id = ?i', $params['product_or_bonus_product']);
            $condition .=' AND (' . fn_find_array_in_set([$params['product_or_bonus_product']], "products", false) . ' OR ' . fn_find_array_in_set([$params['product_or_bonus_product']], "bonus_products", false) . ' OR ' . fn_find_array_in_set($category_ids, "condition_categories", false) . ')';
        }
    }
}

function fn_promotion_motivation_get_product_data_post(&$product_data, $auth, $preview, $lang_code) {
    if (!empty($product_data['product_id']) && SiteArea::isStorefront(AREA)) {
        list($promotions) = fn_get_promotions(['product_or_bonus_product' => $product_data['product_id'], 'usergroup_ids' => Tygh::$app['session']['auth']['usergroup_ids'], 'active' => true]);

        if ($promotions) {
            $promotion = reset($promotions);
            $product_data['promo_text'] = "<div class='ty-promotion-motivation__body'>".$promotion['detailed_description']."</div>";
            if (!empty(trim($product_data['promo_text']))) {
                $product_data['promo_text'] = '<div class="ty-promotion-motivation"><div class="ty-promotion-motivation__title">' . __('promo_subheader') . '</div>' . $product_data['promo_text']."</div>";
            }
        }
        // correct after November 2020
        if (defined('API')) $product_data['promo_text_plain'] = $product_data['promo_text'] = strip_tags($product_data['promo_text']);
    }
}

function fn_promotion_motivation_get_products_before_select(&$params, $join, &$condition, $u_condition, $inventory_join_cond, $sortings, $total, $items_per_page, $lang_code, $having) {
    if (
        !empty($params['promotion_pid'])
        && !empty($params['block_data']['content']['items']['filling'])
        && $params['block_data']['content']['items']['filling'] === 'promotion_products'
    ) {
        list($promotions, ) = list($promotions, ) = fn_get_promotions(['product_or_bonus_product' => $params['promotion_pid'], 'usergroup_ids' => Tygh::$app['session']['auth']['usergroup_ids'], 'active' => true, 'track' => true], 10);

        if ($promotions) {
            $promotion = reset($promotions);
            $promotion_product_ids  = explode(',', $promotion['products']);
            $promotion_category_ids = explode(',', $promotion['condition_categories']);

            $promotion_product_ids = array_merge(
                $promotion_product_ids,
                db_get_fields('SELECT product_id FROM ?:products_categories WHERE category_id IN (?n)', $promotion_category_ids)
            );

            $params['pid'] = implode(',', array_unique($promotion_product_ids));
        } else {
            // To skip get products request
            $params['force_get_by_ids'] = true;
            unset($params['pid'], $params['product_id'], $params['get_conditions']);
        }
    }
}

function fn_promotion_motivation_calculate_cart_post($cart, $auth, $calculate_shipping, $calculate_taxes, $options_style, $apply_cart_promotions, &$cart_products, $product_groups) {
    if (!defined('API')) {
        $applied_promotions = array_keys($cart['applied_promotions']);
        foreach ($cart_products as &$product) {
            list($promotions, ) = fn_get_promotions(['product_or_bonus_product' => $product['product_id'], 'zone' => 'cart', 'usergroup_ids' => $auth['usergroup_ids'], 'active' => true, 'track' => true, 'exclude_promotion_ids' => $applied_promotions], 10);

            if ($promotions) {
                $product['participates_in_promo'] = reset($promotions);
            }
        }
    }
}
