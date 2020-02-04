<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }


function fn_debt_limit_user_init(&$auth, &$user_info)
{
	$res = db_get_row('SELECT debt, ?:users.limit FROM ?:users WHERE user_id = ?i', $auth['user_id']);
	$auth['debt'] = $user_info['debt'] = $res['debt'];
	$auth['limit'] = $user_info['limit'] = $res['limit'];
}
