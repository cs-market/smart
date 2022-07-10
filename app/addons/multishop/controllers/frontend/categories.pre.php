<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'view') {
	if (!$auth['user_id'] && !isset($auth['tmp_usergroups'])) {
		$auth['tmp_usergroups'] = $auth['usergroup_ids'];
		$auth['usergroup_ids'] = Registry::get('runtime.shop_usergroups');
	}
}