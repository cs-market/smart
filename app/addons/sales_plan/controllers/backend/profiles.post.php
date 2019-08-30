<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'update') {
	$condition = '';
	if (Registry::get('runtime.company_id')) {
		$condition .= db_quote(' AND company_id = ?i', Registry::get('runtime.company_id'));
	}
	$plan = db_get_array("SELECT * from ?:sales_plan WHERE user_id =?i $condition", $_REQUEST['user_id']);
	Tygh::$app['view']->assign('plan', $plan);
}