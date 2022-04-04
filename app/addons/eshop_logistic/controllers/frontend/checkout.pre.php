<?php

use Tygh\Tygh;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$cart = & Tygh::$app['session']['cart'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'checkout') {

    if (!empty($_REQUEST['payment_id'])) {

        $payment_info = fn_get_payment_method_data($_REQUEST['payment_id']);
        
        if (!empty($payment_info['eshop_payment_type'])) {
            $cart['payment_method_data']['eshop_changed_payment'] = $payment_info['eshop_payment_type'];
            $cart['calculate_shipping'] = true;
        }
                
    }
}