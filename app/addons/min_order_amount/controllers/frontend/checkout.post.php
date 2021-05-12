<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$cart = & Tygh::$app['session']['cart'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return ;
}

if ($mode == 'checkout' && $cart['min_order_failed']) {
    fn_set_notification('N', __('notice'), $cart['min_order_notification']);
}
