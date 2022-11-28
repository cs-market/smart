<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$cart = & Tygh::$app['session']['cart'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'add' && $action == 'wishlist') {
        $_REQUEST['product_data'] = Tygh::$app['session']['wishlist']['products'];
    }
    return array(CONTROLLER_STATUS_OK);
}

if ($mode == 'complete' && !empty($_REQUEST['order_id'])) {
    return array(CONTROLLER_STATUS_REDIRECT, 'orders.details?order_id='.$_REQUEST['order_id']);
}
