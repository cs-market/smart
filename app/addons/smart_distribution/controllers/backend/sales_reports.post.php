<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'set_report_view') {
		$dynamic_conditions = array_filter($_REQUEST['dynamic_conditions']);
		if ($dynamic_conditions) {
			$suffix = ".view?report_id=$_REQUEST[report_id]" . '&' . http_build_query(array('dynamic_conditions' => $dynamic_conditions));
    		return array(CONTROLLER_STATUS_OK, 'sales_reports' . $suffix);
		}
    }
}

if ($mode == 'view'){
	if (isset($_REQUEST['dynamic_conditions'])) {
		$tabs = Registry::get('navigation.tabs');
		$dynamic_conditions = array_filter($_REQUEST['dynamic_conditions']);
	    foreach ($tabs as &$table) {
	    	$table['href'] .= '&' . http_build_query(array('dynamic_conditions' => $dynamic_conditions));
	    }
	    Tygh::$app['view']->assign('dynamic_conditions', $dynamic_conditions);
	    $tabs = Registry::set('navigation.tabs', $tabs);
	}
}