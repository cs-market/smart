<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }
$wishlist = & Tygh::$app['session']['wishlist'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	return ;
}

if ($mode == 'view') {
	$products = Tygh::$app['view']->getTemplateVars('products');
	foreach ($products as $key => &$product) {
		if (isset($wishlist['products'][$key]['amount'])) {
			$product['selected_amount'] = $wishlist['products'][$key]['amount'];
		}
	}
	Tygh::$app['view']->assign('products', $products);
}