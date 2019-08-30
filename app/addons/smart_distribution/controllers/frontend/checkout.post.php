<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$cart = & Tygh::$app['session']['cart'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	return ;
}

if ($mode == 'checkout') {	
	foreach ($cart['product_groups'] as $group) {
		$min_order_amount = db_get_field('SELECT min_order_amount FROM ?:companies WHERE company_id = ?i', $group['company_id']);
		if ($min_order_amount && $min_order_amount > $group['package_info']['C']) {
	        Tygh::$app['view']->assign('value', $min_order_amount);
	        $min_amount = Tygh::$app['view']->fetch('common/price.tpl');
	        fn_set_notification('W', __('notice'), __('text_min_products_amount_required') . ' ' . $min_amount . ' ' . __('with_company') . ' ' . $group['name']);
	        return array(CONTROLLER_STATUS_REDIRECT, 'checkout.cart');
		}
	}
}