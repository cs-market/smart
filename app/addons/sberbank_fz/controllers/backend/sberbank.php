<?php

use Tygh\Payments\Processors\SberbankFz as Sberbank;

if ($mode == 'cron_payment_notification') {
    $params = ['status' => 'N', 'payments' => array_keys($payments), 'time_from' => mktime(0,0,0), 'time_to' => time()- 60 * 10, 'period' => 'C'];

    list($orders) = fn_get_orders($params);

    if (!empty($orders)) {
        foreach ($orders as $order) {
            Sberbank::getPaymentResult($order['order_id']);
        }
    }
    exit();
}
