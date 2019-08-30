<?php

use Tygh\Registry;

if ( !defined('AREA') ) { die('Access denied'); }

function fn_auth_by_login_auth_routines($request, $auth, &$field, &$condition, $user_login) {
	$_field = ' ( ' . $field;
	$_condition .= db_quote(" OR user_login = ?s )", $user_login) . $condition;
	$user_data = db_get_row("SELECT * FROM ?:users WHERE $_field = ?s" . $_condition, $user_login);
	if ($user_data) {
		$field = ' ( ' . $field;
		$condition .= db_quote(" OR user_login = ?s )", $user_login) . $condition;
	}
}