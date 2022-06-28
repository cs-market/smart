<?php

use Tygh\Registry;

function fn_user_id_to_login($value) {
    return db_get_field('SELECT user_login FROM ?:users WHERE user_id = ?i', $value);
}

function fn_login_to_user_id($value, $row) {
    if (!($company_id = Registry::get('runtime.company_id'))) {
        $company_id = $row['company_id'];
    }
    $condition = '';
    if ($company_id) {
        $condition = db_quote(' AND company_id = ?i', $company_id);
    }
    // add user_role condition

    return db_get_field("SELECT user_id FROM ?:users WHERE user_login = ?s $condition", $value);
}
