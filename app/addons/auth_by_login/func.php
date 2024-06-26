<?php

use Tygh\Registry;

if ( !defined('AREA') ) { die('Access denied'); }

function fn_auth_by_login_auth_routines($request, $auth, &$field, &$condition, &$user_login) {
    if (!empty($user_login)) {
        $users_data = db_get_array("SELECT * FROM ?:users WHERE (email = ?s OR user_login = ?s)" . $condition, $user_login, $user_login);

        if (!empty($users_data)) {
            foreach ($users_data as $user_data) {
                $password = (!empty($request['password'])) ? $request['password']: '';
                $salt = isset($user_data['salt']) ? $user_data['salt'] : '';
                if (fn_user_password_verify((int) $user_data['user_id'], $password, (string) $user_data['password'], (string) $salt)) {
                    $field = '1';
                    $user_login = '1';
                    $condition .= db_quote(" AND user_id = ?i", $user_data['user_id']);
                    break;
                }
            }
        }
    }
}

function fn_auth_by_login_user_exist($user_id, $user_data, &$condition) {
    if (!empty($user_data['company_id']) && !empty($user_data['user_login'])) {
        $condition = db_quote(' user_login = ?s AND company_id = ?i', $user_data['user_login'], $user_data['company_id']);
        $condition .= db_quote(" AND user_id != ?i", $user_id);
    }
}
