<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$cart = & Tygh::$app['session']['cart'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if ($mode == 'add' && $action == 'wishlist') {
		$_REQUEST['product_data'] = Tygh::$app['session']['wishlist']['products'];
	}
	return array(CONTROLLER_STATUS_OK);
}