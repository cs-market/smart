<?php

use Tygh\Registry;
use Tygh\Enum\YesNo;
use Tygh\Enum\SiteArea;
use Tygh\Enum\NotificationSeverity;
use Tygh\Enum\RewardPointsMechanics;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_extended_reward_points_update_product_post($product_data, $product_id) {
    if (isset($product_data['reward_points']) && YesNo::toBool($product_data['is_op'])) {
        $usergroup_ids = array_column($product_data['reward_points'], 'usergroup_id');
        if (!empty($usergroup_ids)) {
            db_query('DELETE FROM ?:reward_points WHERE object_id = ?i AND object_type = ?s AND usergroup_id NOT IN (?a)', $product_id, PRODUCT_REWARD_POINTS, $usergroup_ids);
        }
    }
}

function fn_extended_reward_points_gather_additional_product_data_before_discounts(&$product, $auth, $params) {
    if (SiteArea::isStorefront(AREA) && YesNo::toBool($product['is_pbp']) && RewardPointsMechanics::isFullPayment($auth['extended_reward_points']['reward_points_mechanics'])) {
        $product['point_price'] = fn_extended_reward_points_get_price_in_points($product, $auth);
    }
}

function fn_extended_reward_points_pre_get_cart_product_data($hash, $product, $skip_promotion, $cart, $auth, $promotion_amount, &$fields, &$join, $params) {
    $fields[] = '?:products.is_pbp';
    $fields[] = '?:products.is_oper';
    $fields[] = '?:products.is_op';
}

function fn_extended_reward_points_get_cart_product_data($product_id, &$_pdata, &$product, $auth, $cart, $hash) {
    $product['is_pbp'] = $_pdata['is_pbp'];
    $product['is_oper'] = $_pdata['is_oper'];
}

function fn_extended_reward_points_calculate_cart_taxes_pre(&$cart, &$cart_products, $shipping_rates, $calculate_taxes, $auth) {
    if (!empty($auth['extended_reward_points'])) {
        if (RewardPointsMechanics::isPartialPayment($auth['extended_reward_points']['reward_points_mechanics'])) {
            $min_original_subtotal = $cart['original_subtotal'];
            $min_subtotal = $max_rp = 0;

            foreach ($cart['products'] as &$product) {
                $min_subtotal += $product['price'] * $product['amount'] * (1 - ($auth['extended_reward_points']['max_product_discount'] / 100));
                $max_rp += round($product['price'] * $product['amount'] * $auth['extended_reward_points']['max_rp_discount'] / 100);
                unset($product['extra']['points_info']['price']);
            }
            unset($product);

            $max_products_discount = round($cart['subtotal'] - $min_subtotal);

            $cart['points_info']['max_reward_points'] = min($max_rp, $max_products_discount);
            if ($cart['points_info']['in_use']['points'] > $cart['points_info']['max_reward_points']) {
                $cart['points_info']['in_use']['points'] = $cart['points_info']['max_reward_points'];
                fn_set_notification(NotificationSeverity::NOTICE, __('notice'), __('extended_reward_points.points_amount_reduced'));
            }
        } else {
            foreach ($cart['products'] as $key => &$data) {
                if (!YesNo::toBool($data['is_pbp'])) continue;
                $data['extra']['point_price'] = fn_extended_reward_points_get_price_in_points($data, $auth);
                $cart_products[$key]['price'] = 0;
            }
            unset($data);

            $cart['extended_points_info']['in_use'] = fn_get_cart_points_in_use($cart);
        }
    }
}

function fn_extended_reward_points_pre_place_order($cart, &$allow, $product_groups) {
    $balance = Tygh::$app['session']['auth']['points'] - fn_get_cart_points_in_use($cart);
    if ($balance < 0) {
        $allow = false;
        fn_set_notification(NotificationSeverity::WARNING, __('WARNING'), __('extended_reward_points.not_enough_points', [abs($balance)]));
    }
}

function fn_extended_reward_points_get_order_info(&$order_info, $additional_data) {
    if (!empty($order_info) && $points = fn_get_cart_points_in_use($order_info)) {
        $order_info['points_info']['in_use']['points'] = $points;
    }
}

function fn_extended_reward_points_user_init(&$auth, $user_info) {
    if (empty($auth['extended_reward_points'])) {
        if (!empty($user_info['extended_reward_points'])) {
            $auth['extended_reward_points'] = $user_info['extended_reward_points'];
        } else {
            $auth['extended_reward_points'] = db_get_row('SELECT reward_points_mechanics, max_rp_discount, max_product_discount FROM ?:companies WHERE company_id = ?i', $auth['company_id']);
        }
    }
}

// vega
function fn_extended_reward_points_add_product_to_cart_get_price($product_data, $cart, $auth, $update, $_id, &$data, $product_id, $amount, $price, $zero_price_action, &$allow_add) {
    if (RewardPointsMechanics::isFullPayment($auth['extended_reward_points']['reward_points_mechanics']) && $allow_add) {
        if (!empty($cart['products'][$_id])) {
            $data['is_pbp'] = $cart['products'][$_id]['is_pbp'];
            $data['extra']['point_price'] = $cart['products'][$_id]['extra']['point_price'];
        } else {
            $data = fn_array_merge($data, db_get_row('SELECT is_pbp, is_op, is_oper FROM ?:products WHERE product_id = ?i', $product_id));
        }

        if (YesNo::toBool($data['is_pbp'])) {
            $data['extra']['is_pbp'] = $data['is_pbp'];
            if (!empty($cart['products'][$_id])) {
                $data['extra']['point_price'] = $cart['products'][$_id]['extra']['point_price'];
            } else {
                $tmp = $data;
                $tmp['price'] = $price;
                $data['extra']['point_price'] = fn_extended_reward_points_get_price_in_points($tmp, $auth);
            }
            
            $in_use = fn_get_cart_points_in_use($cart, $product_id);
            
            $balance = $auth['points'] - $data['extra']['point_price'] * $amount - $in_use;
            if ($balance < 0) {
                fn_set_notification(NotificationSeverity::WARNING, __('WARNING'), __('extended_reward_points.not_enough_points', [abs($balance)]));
                $allow_add = false;
            }
        }
    }
}

function fn_extended_reward_points_get_price_in_points($product, $auth) {
    if (YesNo::toBool(Registry::get('addons.reward_points.auto_price_in_points')) && !YesNo::toBool($product['is_oper'])) {
        $per = Registry::get('addons.reward_points.point_rate');

        $subtotal = $product['price'];
        if (!YesNo::toBool(Registry::get('addons.reward_points.price_in_points_with_discounts')) && isset($product['discount'])) {
            $subtotal = $product['price'] + $product['discount'];
        }
    } else {
        $per = (!empty($product['price']) && floatval($product['price'])) ? fn_get_price_in_points($product['product_id'], $auth) / $product['price'] : 0;
        $subtotal = $product['price'];
    }

    return ceil($subtotal);
}

function fn_get_cart_points_in_use($cart, $exclude_products = []) {
    if (!is_array($exclude_products)) {
        $exclude_products = [$exclude_products];
    }

    $total_use_points = 0;
    if (!empty($cart['products']))
    foreach ($cart['products'] as &$product) {
        if (in_array($product['product_id'], $exclude_products) || !YesNo::toBool($product['extra']['is_pbp'])) continue;

        $total_use_points += $product['extra']['point_price'] * $product['amount'];
    }

    return $total_use_points;
}

//vega
function fn_get_available_points($cart) {
    if (empty($cart)) $cart = Tygh::$app['session']['cart'];

    return max(
        Tygh::$app['session']['auth']['points'] - @$cart['extended_points_info']['in_use'],
        0
    );
}
