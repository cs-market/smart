<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'view') {
	if (isset($auth['tmp_usergroups'])) {
		$auth['usergroup_ids'] = $auth['tmp_usergroups'];
		unset($auth['tmp_usergroups']);
	}
}