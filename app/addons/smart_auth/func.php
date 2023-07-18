<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

function fn_settings_variants_addons_smart_auth_auth_fields() {
    $fields = [];
    $data = fn_get_profile_fields();
    foreach ($data as $section) {
        $fields = $fields + $section;
    }

    return ['user_login' => __('login')] + array_column($fields, 'description', 'field_name');
}

function fn_smart_auth_auth_routines($request, $auth, &$field, &$condition, &$user_login) {
    if (!empty(trim($user_login))) {
        $login_fields = Registry::get('addons.smart_auth.auth_fields');
        if (empty($login_fields)) return;
        $where = [];
        foreach ($login_fields as $field => &$data) {
            if (strpos($field, 'phone') !== false) {
                //temp
                $phone = trim($user_login);
                $phone = preg_replace('/[^0-9]/', '', $phone);
                if (strlen($phone) > 10) $phone = preg_replace('/^[7,8]/', '', $phone);
                if (strlen($phone) < 10) continue;
                $phone = '%' . $phone;
                $where[] = db_quote(
                    "(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE($field, ' ', ''), '-', ''), '+', ''), '(', ''), ')', '') LIKE ?l)", $phone
                );
            } else {
                $where[] = db_quote(" $field = ?s ", $user_login);
            }
        }

        $pre_condition = ' ( ' . implode(' OR ', $where) . ' ) ';

        fn_set_hook('smart_auth_auth_routines', $pre_condition, $request, $auth, $field, $condition, $user_login);

        $users_data = db_get_array("SELECT * FROM ?:users WHERE ?p ?p", $pre_condition, $condition);

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

function fn_smart_auth_user_exist($user_id, $user_data, &$condition) {
    $user_data['company_id'] = !empty($user_data['company_id']) ? $user_data['company_id'] : Registry::get('runtime.company_id');
    if (empty($user_data['user_login'])) {
        $user_data['user_login'] = db_get_field("SELECT user_login FROM ?:users WHERE user_id = ?i", $user_id);
    }

    if (!empty($user_data['company_id']) && !empty($user_data['user_login'])) {
        $condition = db_quote(' user_login = ?s AND company_id = ?i', $user_data['user_login'], $user_data['company_id']);
        $condition .= db_quote(" AND user_id != ?i", $user_id);
    }
}
