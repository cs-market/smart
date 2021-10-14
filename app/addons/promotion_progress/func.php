<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_settings_variants_addons_promotion_progress_order_statuses() {
    return fn_get_simple_statuses('O');
}

function fn_promotion_progress_get_promotions($params, $fields, $sortings, &$condition, $join, $group, $lang_code) {
    if (isset($params['progressed_promotions'])) {
        $condition .= db_quote(' AND (?:promotions.conditions_hash LIKE ?l OR ?:promotions.conditions_hash LIKE ?l OR ?:promotions.conditions_hash LIKE ?l)', '%progress_total_paid=%', '%progress_order_amount=%', '%progress_purchased_products=%');
        //TODO add/check usergroup or vendor condition
    }

}

function fn_promotion_progress_pre_promotion_validate($promotion_id, $promotion, $data, &$stop_validating, &$result, $auth, $cart_products) {
    if ($promotion['condition'] == 'progress_period') {
        //process it later
        $stop_validating = $result = true;
    }
}

//fn_get_progressed_promotions();
function fn_get_progressed_promotions() {
    list($promotions) = fn_get_promotions(['progressed_promotions' => true]);
    foreach($promotions AS $promotion) {
        $cond = unserialize($promotion['conditions']);
    }
}

function fn_promotion_validate_promotion_progress($promotion_id, $promo, $auth) {
    if (in_array($promo['condition'], ['progress_total_paid', 'progress_order_amount'])) {
        $promo_original = fn_get_promotion_data($promotion_id);
        $progress_period = fn_find_progress_period($promo_original['conditions']);
    }

    if ($promo['condition'] == 'progress_total_paid') {
        fn_print_die($progress_period, $promo);
    }
}

function fn_find_progress_period($conditions_group) {
    foreach ($conditions_group['conditions'] as $i => $group_item) {
        if (isset($group_item['conditions'])) {
            return fn_find_progress_period($group_item);
        } elseif ($group_item['condition'] == 'progress_period') {
            return $group_item;
        } else {
            return false;
        }
    }
}