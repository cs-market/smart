<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_get_extra_user_data() {
    return fn_get_user_additional_data(USER_EXTRA_DATA, Tygh::$app['session']['auth']['user_id']);
}

function fn_draft_master_update_profile($action, $user_data, $current_user_data) {
    if (!empty($user_data['extra_data'])) {
        fn_save_user_additional_data(USER_EXTRA_DATA, $user_data['extra_data'], $user_data['user_id']);
    }
}
