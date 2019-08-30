<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if ( $mode == 'update' && !empty($_REQUEST['assing_users'] && $_REQUEST['usergroup_id'])) {
		$users = explode(',', $_REQUEST['assing_users']);
		foreach ($users as $user_id) {
			fn_change_usergroup_status('A', $user_id, $_REQUEST['usergroup_id'], fn_get_notification_rules(array()));
		}
	}
}