<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	return;
}
if ($mode == 'login_form') {
	if (isset($_SESSION['custom_registration'])) {
		$schema = fn_get_schema('user_from_form', 'schema');
		$company_id = $_SESSION['custom_registration'] ;
		$pages = array_filter($schema, function($v, $k) use ($company_id) {

			return $v['company_id'] == $company_id;
		}, ARRAY_FILTER_USE_BOTH);
		list($pages, ) = fn_get_pages(array('item_ids' => implode(',', array_keys($pages))));
		Tygh::$app['view']->assign('registration_pages', $pages);
	}
}