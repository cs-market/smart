<?php

use Tygh\Registry;
use Tygh\Enum\NotificationSeverity;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_settings_variants_addons_locked_users_usergroup_id() {
    if ($usergroups = fn_get_usergroups(array('type' => 'C', 'status' => array('A', 'H')))) {
        return array_column($usergroups, 'usergroup', 'usergroup_id');
    } 
    return [];
}

function fn_locked_users_update_user_pre($user_id, $user_data, $auth, $ship_to_another, $notify_user, &$can_update) {
    fn_locked_users_api_disable_user($can_update, $user_id, $user_data['user_type']);
    if (!$can_update) {
        fn_set_notification(NotificationSeverity::ERROR, __('error'), __('denied'));
        if (isset($_POST['user_data'])) unset($_POST['user_data']);
    }
}

function fn_locked_users_api_disable_user(&$can_update, $user_id, $user_type = 'C') {
    if (!empty($user_id)) {
        $usergroup_ids = fn_define_usergroups([
            'user_id'   => $user_id,
            'user_type' => $user_type
        ]);

        $can_update = $can_update && !in_array(Registry::get('addons.locked_users.usergroup_id'), $usergroup_ids);
    }
}
