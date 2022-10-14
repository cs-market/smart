<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_draft_master_get_user_info(&$user_id, &$get_profile, &$profile_id, &$user_data) {
    if (empty($user_data['extra_data'])) {
        $user_data['extra_data'] = fn_get_user_additional_data(USER_EXTRA_DATA, $user_data['user_id']);
    }
}

function fn_draft_master_user_init($auth, &$user_info) {
    if (empty($user_info['extra_data']) && !empty($user_info['user_id'])) {
        $user_info['extra_data'] = fn_get_user_additional_data(USER_EXTRA_DATA, $user_info['user_id']);
    }
}
