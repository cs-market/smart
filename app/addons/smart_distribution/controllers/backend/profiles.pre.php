<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	return ;
}

if ( ($mode == 'update' || $mode == 'add')) {
	$managers = fn_smart_distribution_get_managers(array('user_id' => (isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0)));
	Tygh::$app['view']->assign('managers', $managers);
}