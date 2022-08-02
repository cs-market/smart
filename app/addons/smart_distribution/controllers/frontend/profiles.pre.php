<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'delete_profile') {
        db_query('UPDATE ?:users SET password = ?s WHERE user_id = ?i', fn_generate_password(4), $auth['user_id']);
        fn_user_logout($auth);
        fn_set_notification('N', __('notice'), __('text_profile_deleted'));
    }
	return;
}

if ($mode == 'add') {
    return [CONTROLLER_STATUS_REDIRECT, $_SERVER['HTTP_REFERER'] ?? fn_url()];
}
