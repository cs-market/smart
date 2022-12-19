<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if (defined('PAYMENT_NOTIFICATION')) {

} else {
    if (!empty($processor_data['processor_params']['account_order_status'])) {
        $pp_response = array(
            'order_status' => $processor_data['processor_params']['account_order_status']
        );
    } else {
        $pp_response = array(
            'order_status' => 'O'
        );
    }
}
