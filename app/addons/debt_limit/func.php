<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_debt_limit_get_user_short_info_pre($user_id, &$fields, $condition, $join, $group_by) {
    $fields[] = 'debt';
    $fields[] = '?:users.limit';
}

function fn_debt_limit_user_init(&$auth, $user_info)
{
    // TODO get this data not from auth but from registry user info in templates etc
    if (isset($user_info['debt'])) $auth['debt'] = $user_info['debt'];
    if (isset($user_info['limit'])) $auth['limit'] = $user_info['limit'];
}

function fn_debt_limit_exim_1c_update_order($order_data, $cml) {
    if (isset($order_data -> {$cml['contractors']} -> {$cml['contractor']} -> {$cml['debt']}) && !empty($order_data -> {$cml['contractors']} -> {$cml['contractor']} -> {$cml['debt']})) {
        $udata['debt'] = strval($order_data -> {$cml['contractors']} -> {$cml['contractor']} -> {$cml['debt']});
    }
    
    if (isset($order_data -> {$cml['contractors']} -> {$cml['contractor']} -> {$cml['debt_limit']}) && !empty($order_data -> {$cml['contractors']} -> {$cml['contractor']} -> {$cml['debt_limit']})) {
        $udata['debt_limit'] = strval($order_data -> {$cml['contractors']} -> {$cml['contractor']} -> {$cml['debt_limit']});
    }
    if (!empty($udata)) {
        array_walk($udata, function(&$value, &$key) {
            $value = str_replace(',', '.', $value);
        });
        $order_id = strval($order_data->{$cml['number']});
        $user_id = db_get_field('SELECT user_id FROM ?:orders WHERE order_id = ?i', $order_id);
        db_query('UPDATE ?:users SET ?u WHERE user_id = ?i', $udata, $user_id);
    }
}
