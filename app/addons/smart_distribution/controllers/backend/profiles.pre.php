<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if ( ($mode == 'update' || $mode == 'add') && ($_REQUEST['user_type'] == 'C')) {
		$managers = fn_smart_distribution_get_managers(array('user_id' => $_REQUEST['user_id']));
		db_query("DELETE FROM ?:vendors_customers WHERE customer_id = ?i AND vendor_manager IN (?a)", $_REQUEST['user_id'], array_keys($managers));

		$u_data = array();
		if ($_REQUEST['managers']) {
			$managers = explode(',', $_REQUEST['managers']);
			foreach ($managers as $m_id) {
				$udata[] = array('vendor_manager' => $m_id, 'customer_id' => $_REQUEST['user_id']);
			}
			db_query('INSERT INTO ?:vendors_customers ?m', $udata);
		}
	}
	return array(CONTROLLER_STATUS_OK);
}

if ( ($mode == 'update' || $mode == 'add')) {
	$managers = fn_smart_distribution_get_managers(array('user_id' => $_REQUEST['user_id']));
	Tygh::$app['view']->assign('managers', $managers);
}