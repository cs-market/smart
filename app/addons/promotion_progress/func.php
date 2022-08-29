<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_settings_variants_addons_promotion_progress_order_statuses() {
    return fn_get_simple_statuses('O');
}

function fn_promotion_progress_get_promotions($params, $fields, $sortings, &$condition, $join, $group, $lang_code) {
    if (isset($params['progressed_promotions'])) {
        $condition .= db_quote(' AND (?:promotions.conditions_hash LIKE ?l OR ?:promotions.conditions_hash LIKE ?l OR ?:promotions.conditions_hash LIKE ?l OR ?:promotions.conditions_hash LIKE ?l OR ?:promotions.conditions_hash LIKE ?l)', '%progress_total_paid=%', '%progress_order_amount=%', '%progress_average_paid=%', '%progress_purchased_unique_sku=%', '%progress_purchased_total_amount=%');
        //TODO add/check usergroup or vendor condition
    }
}

function fn_promotion_progress_pre_promotion_validate($promotion_id, $promotion, $data, &$stop_validating, &$result, $auth, $cart_products) {
    if ($promotion['condition'] == 'progress_period') {
        //process it later
        $stop_validating = $result = true;
    }
}

function fn_promotion_validate_promotion_progress($promotion_id, $promo, $auth, $promo_original = false) {
    if ($auth['user_id']) {
        $join = '';
        if ($promo['condition'] == 'progress_total_paid') {
            if ($value = fn_get_user_additional_data('S', $auth['user_id'])) {
                return $value;
            }
            $field = 'sum(o.total)';
        }
        if ($promo['condition'] == 'progress_order_amount') {
            $field = 'count(o.order_id)';
        }
        if ($promo['condition'] == 'progress_average_paid') {
            $field = 'AVG(o.total)';
        }
        if ($promo['condition'] == 'progress_purchased_unique_sku') {
            $field = 'count(distinct(od.product_id))';
            $join = db_quote(' LEFT JOIN ?:order_details AS od ON od.order_id = o.order_id '); 
        }
        if ($promo['condition'] == 'progress_purchased_total_amount') {
            $field = 'sum(od.amount)';
            $join = db_quote(' LEFT JOIN ?:order_details AS od ON od.order_id = o.order_id '); 
        }

        if (in_array($promo['condition'], ['progress_total_paid', 'progress_order_amount', 'progress_average_paid', 'progress_purchased_unique_sku', 'progress_purchased_total_amount'])) {

            $condition['base'] = '1';
            $condition['is_parent'] = db_quote('parent_order_id = ?i', 0);
            $condition['user_id'] = db_quote('user_id = ?i', $auth['user_id']);

            $promo_original = $promo_original ?? fn_get_promotion_data($promotion_id);
            $progress_period = fn_find_promotion_condition($promo_original['conditions'], 'progress_period');
            list($time_from, $time_to) = fn_create_periods(['period'=> $progress_period['value']]);

            if ($time_from) {
                $condition['time_from'] = db_quote('o.timestamp >= ?i', $time_from);
            }
            if ($time_to) {
                $condition['time_to'] = db_quote('o.timestamp <= ?i', $time_to);
            }
            if ($statuses = Registry::get('addons.promotion_progress.order_statuses')) {
                $condition['status'] = db_quote('o.status IN (?a)', array_keys($statuses));
            }

            $condition = implode(' AND ', $condition);

            $value = db_get_field("SELECT $field FROM ?:orders AS o $join WHERE $condition");

            return $value ?? 0;
        }
    }
    return false;
}

if (!is_callable('fn_find_promotion_condition')) {
    function fn_find_promotion_condition(&$conditions_group, $needle, $remove = false) {
        $res = false;
        foreach ($conditions_group['conditions'] as $i => $group_item) {
            if (isset($group_item['conditions'])) {
                $res = fn_find_promotion_condition($conditions_group['conditions'][$i], $needle, $remove);
            } elseif ((is_array($needle) && in_array($group_item['condition'], $needle)) || $group_item['condition'] == $needle) {
                if ($remove) unset($conditions_group['conditions'][$i]);
                $res = $group_item;
            }
            if ($res) return $res;
        }

        return $res;
    }
}

function fn_get_progress_promotions($cart) {
    static $progress_promotions;
    if (!Tygh::$app['session']['auth']['user_id']) return [];
    if (empty($progress_promotions)) {

        list($promotions) = fn_get_promotions(['progressed_promotions' => true]);

        foreach($promotions AS &$promotion) {
            $promotion['conditions'] = unserialize($promotion['conditions']);
            foreach (['progress_total_paid', 'progress_order_amount', 'progress_average_paid', 'progress_purchased_unique_sku', 'progress_purchased_total_amount'] as $progress) {
                if ($progress_condition = fn_find_promotion_condition($promotion['conditions'], $progress)) break;
            }

            $promotion['goal_value'] = $progress_condition['value'];
            $promotion['current_value'] = fn_promotion_validate_promotion_progress($promotion['promotion_id'], $progress_condition, Tygh::$app['session']['auth'], $promotion);
            if (in_array($progress_condition['condition'], ['progress_total_paid', 'progress_average_paid'])) {
                $promotion['modify_values_to_price'] = true;
            }
        }
        $progress_promotions = $promotions;
    }
    if (defined('API')) {
        foreach($promotions AS &$promotion) {
            if ($promotion['modify_values_to_price']) {
                $promotion['start_value_formatted'] = fn_storefront_rest_api_format_price(0, CART_PRIMARY_CURRENCY);
                $promotion['goal_value_formatted'] = fn_storefront_rest_api_format_price($promotion['goal_value'], CART_PRIMARY_CURRENCY);
                $promotion['current_value_formatted'] = fn_storefront_rest_api_format_price($promotion['current_value'], CART_PRIMARY_CURRENCY);
            }
        }
    }

    return $progress_promotions;
}

function fn_promotion_progress_get_user_info($user_id, $get_profile, $profile_id, &$user_data) {
    if (AREA == 'A') {
        $user_data['total_sales'] = fn_get_user_additional_data('S', $user_id);
    }
}

function fn_promotion_progress_update_user_profile_pre($user_id, $user_data, $action) {
    if (isset($user_data['total_sales'])) fn_save_user_additional_data('S', $user_data['total_sales'], $user_id);
}
