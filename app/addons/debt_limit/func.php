<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }


function fn_debt_limit_user_init(&$auth, &$user_info)
{
	$res = db_get_row('SELECT debt, ?:users.limit FROM ?:users WHERE user_id = ?i', $auth['user_id']);
	$auth['debt'] = $user_info['debt'] = $res['debt'];
	$auth['limit'] = $user_info['limit'] = $res['limit'];
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