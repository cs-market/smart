<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_promotion_validate_budget($id, $condition, $cart) {
    if ($condition['value'] > 0) return true;
}

function fn_promotion_budget_change_order_status($status_to, $status_from, $order_info, $force_notification, $order_statuses, $place_order) {
    if (!empty($order_info['subtotal_discount']) && !empty($order_info['promotions'])) {
        foreach($order_info['promotions'] as $promotion_id => $data) {
            $promotion = fn_get_promotion_data($promotion_id);
            if ($condition = fn_find_promotion_condition($promotion['conditions'], 'budget')) {
                if ($order_statuses[$status_to]['params']['inventory'] == 'I' && $order_statuses[$status_from]['params']['inventory'] == 'D') {
                    // increase budget in use
                    $condition['value'] += $order_info['subtotal_discount'];
                    fn_set_promotion_condition_value($promotion['conditions'], 'budget', $condition['value']);
                    $data = [];
                    $data['conditions_hash'] = fn_promotion_serialize($promotion['conditions']['conditions']);
                    $data['conditions'] = serialize($promotion['conditions']);
                    db_query('UPDATE ?:promotions SET ?u WHERE promotion_id = ?i', $data, $promotion_id);
                    return;
                }
                if ($order_statuses[$status_to]['params']['inventory'] == 'D' && $order_statuses[$status_from]['params']['inventory'] == 'I') {
                    $condition['value'] -= $order_info['subtotal_discount'];
                    fn_set_promotion_condition_value($promotion['conditions'], 'budget', $condition['value']);
                    $data = [];
                    $data['conditions_hash'] = fn_promotion_serialize($promotion['conditions']['conditions']);
                    $data['conditions'] = serialize($promotion['conditions']);
                    db_query('UPDATE ?:promotions SET ?u WHERE promotion_id = ?i', $data, $promotion_id);
                    return;
                }
            }
        }
    }
}

if (!is_callable('fn_find_promotion_condition')) {
    function fn_find_promotion_condition(&$conditions_group, $needle, $remove = false) {
        foreach ($conditions_group['conditions'] as $i => $group_item) {
            if (isset($group_item['conditions'])) {
                $res = fn_find_promotion_condition($conditions_group['conditions'][$i], $needle, $remove);
            } elseif ((is_array($needle) && in_array($group_item['condition'], $needle)) || $group_item['condition'] == $needle) {
                if ($remove) unset($conditions_group['conditions'][$i]);
                $res = $group_item;
            }
            if ($res) return $res;
        }

        return false;
    }
}

function fn_set_promotion_condition_value(&$conditions_group, $needle, $value) {
    foreach ($conditions_group['conditions'] as $i => &$group_item) {
        if (isset($group_item['conditions'])) {
            fn_find_promotion_condition($conditions_group['conditions'][$i], $needle, $value);
        } elseif ((is_array($needle) && in_array($group_item['condition'], $needle)) || $group_item['condition'] == $needle) {
            $group_item['value'] = $value;
        }
    }
}
