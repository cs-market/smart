<?php

use Tygh\Registry;
use Tygh\Enum\SiteArea;
use Tygh\Enum\NotificationSeverity;
use Tygh\Enum\RewardPointsMechanics;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_extended_reward_points_get_product_data($product_id, &$field_list, &$join, $auth, $lang_code, $condition, $price_usergroup) {
    $field_list .= db_quote(' , ?:companies.reward_points_mechanics, ?:companies.max_rp_discount');
    $join .= db_quote(' LEFT JOIN ?:companies ON ?:products.company_id = ?:companies.company_id');
}

function fn_extended_reward_points_load_products_extra_data(&$extra_fields, $products, $product_ids, $params, $lang_code) {
    if (SiteArea::isStorefront(AREA)) {
        if (isset($extra_fields['?:companies'])) {
            $extra_fields['?:companies']['fields'][] = 'reward_points_mechanics';
        } else {
            $extra_fields['?:companies'] = [
                'primary_key' => 'product_id',
                'fields' => [
                    'reward_points_mechanics',
                    'product_id' => '?:products.product_id',
                ],
                'join' => db_quote(' LEFT JOIN ?:products ON ?:products.company_id = ?:companies.company_id')
            ];
        }

        $user_groups = array_unique(array_merge(array(USERGROUP_ALL), Tygh::$app['session']['auth']['usergroup_ids']));

        $extra_fields['?:product_point_prices'] = [
            'primary_key' => 'product_id',
            'fields' => [
                'point_price' => 'MIN(point_price)'
            ],
            'condition' => db_quote(' AND ?:product_point_prices.lower_limit = 1 AND ?:product_point_prices.usergroup_id IN (?n)',
            $user_groups
            ),
            'group_by' => 'GROUP BY ?:product_point_prices.product_id'
        ];
    }
}

function fn_get_product_min_prices($product_id, &$product_data, $auth, $get_all = false) {
    $table_name = '?:product_min_prices';
    $condition = '';

    if ($get_all || SiteArea::isAdmin(AREA)) {
        $product_data['min_prices'] = db_get_array("SELECT min_prices.product_id, min_prices.usergroup_id, min_prices.price AS min_price FROM $table_name AS min_prices WHERE product_id = ?i $condition ORDER BY usergroup_id", $product_id);
    }
}

function fn_extended_reward_points_update_product_post($product_data, $product_id, $lang_code, $create) {
    if (isset($product_data['min_prices'])) {
        $table_name = '?:product_min_prices';
        $skip_price_delete = false;
        $condition = '';

        if (!$skip_price_delete) {
            db_query("DELETE FROM $table_name WHERE product_id = ?i $condition", $product_id);
        }

        array_walk($product_data['min_prices'], function(&$v) use ($product_id) {
            $v['product_id'] = $product_id;
        });
        $product_data['min_prices'] = array_filter($product_data['min_prices'], function($v) {
            return $v['price'] || $v['usergroup_id'];
        });
        if (!empty($product_data['min_prices'])) {
            db_query("REPLACE INTO $table_name ?m", $product_data['min_prices']);
        }
    }
}

function fn_extended_reward_points_pre_get_cart_product_data($hash, $product, $skip_promotion, $cart, $auth, $promotion_amount, &$fields, &$join, $params) {
    $fields[] = '?:companies.reward_points_mechanics';
    $fields[] = '?:companies.max_rp_discount';
    $fields[] = 'min(?:product_min_prices.price) as min_price';
    $join .= db_quote(' LEFT JOIN ?:product_min_prices ON ?:product_min_prices.product_id = ?:products.product_id AND ?:product_min_prices.usergroup_id IN (?a) ', $auth['usergroup_ids']);
}

function fn_extended_reward_points_get_cart_product_data($product_id, &$_pdata, &$product, $auth, $cart, $hash) {
    $_pdata['min_price'] = $product['min_price'] = !empty($_pdata['min_price'])? $_pdata['min_price'] : 0;
    $product['reward_points_mechanics'] = $_pdata['reward_points_mechanics'];
    $product['max_rp_discount'] = $_pdata['max_rp_discount'];

}

function fn_extended_reward_points_calculate_cart_taxes_pre(&$cart, &$cart_products, &$shipping_rates, &$calculate_taxes, &$auth) {
    $reward_points_mechanics = reset(array_column($cart_products, 'reward_points_mechanics'));

    if (empty($reward_points_mechanics)) return;

    if (RewardPointsMechanics::isPartialPayment($reward_points_mechanics)) {
        $min_original_subtotal = $cart['original_subtotal'];
        $min_subtotal = $max_rp = 0;
        foreach ($cart['products'] as &$product) {
            $min_subtotal += $product['min_price'] * $product['amount'];
            $max_rp += round($product['price'] * $product['amount'] * $product['max_rp_discount'] / 100);
            unset($product['extra']['points_info']['price']);
        }
        $max_products_discount = round($cart['subtotal'] - $min_subtotal);
        $cart['points_info']['max_reward_points'] = min($max_rp, $max_products_discount);
        if ($cart['points_info']['in_use']['points'] > $cart['points_info']['max_reward_points']) {
            $cart['points_info']['in_use']['points'] = $cart['points_info']['max_reward_points'];
            fn_set_notification(NotificationSeverity::NOTICE, __('notice'), __('extended_reward_points.points_amount_reduced'));
        }
    } else {
        foreach ($cart_products as $key => $value) {
            // code...
        }
        //fn_get_price_in_points($product_id, $auth)
    }
}

function fn_extended_reward_points_check_add_to_cart_post($cart, $product, $product_id, &$result)
{
    // if (!isset($product['pay_by_points']) || $product['pay_by_points'] != 'Y') {
    //     return;
    // }

    // $is_pbp = db_get_field("SELECT is_pbp FROM ?:products WHERE product_id = ?i", $product_id);

    // // if pay by points unavailable
    // if ($is_pbp != 'Y') {
    //     fn_set_notification('E', __('error'), __(
    //         'pay_by_points__notification__pbp_unavailable',
    //         ['%product%' => fn_get_product_name($product['product_id'])]
    //     ));
    //     $result = false;
    // }
}

function fn_get_available_points($cart)
{
    //$user_info = Registry::get('user_info');
    if (empty($cart)) $cart = Tygh::$app['session']['cart'];
    return max(
        fn_get_user_additional_data(POINTS) - $cart['pay_by_points']['in_use'],
        0
    );
}

function fn_extended_reward_points_reward_points_calculate_item($cart_products, $cart, $k, $v, &$reward_coef) {
    $reward_coef = 1;
}
