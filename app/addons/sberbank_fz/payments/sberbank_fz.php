<?php

use Tygh\Payments\Processors\SberbankFz as Sberbank;

if (defined('PAYMENT_NOTIFICATION')) {

    $order_id = 0;
    if (!empty($_REQUEST['ordernumber'])) {
        $order_id = $_REQUEST['ordernumber'];
    }

    if ($mode == 'return' || $mode == 'error') {
        Sberbank::getPaymentResult($order_id, $_REQUEST['orderId']);

        if (isset($_REQUEST['isMobilePayment']) && $_REQUEST['isMobilePayment']) {
            if (Tygh::$app['session']['auth']['user_id']) {
                echo(__('processing_order'));
            } else {
                echo(__('addons.sberbank_fz.order_placed_mobile'));
            }
        } else {
            fn_order_placement_routines('route', $order_id, false);
        }
    }

    exit;

} else {
    $sberbank = new Sberbank($processor_data);

    $response = $sberbank->register($order_info);

    if (!empty($processor_data['processor_params']['logging']) && $processor_data['processor_params']['logging'] == 'Y') {
        Sberbank::writeLog($response, 'sberbank.log');
    }

    if (!$sberbank->isError()) {

        $pp_response = array(
            'transaction_id' => $response['orderId']
        );

        fn_update_order_payment_info($order_id, $pp_response);
        fn_create_payment_form($response['formUrl'], array(), 'SberBank Online', true, 'GET');

    } else {
        $pp_response['order_status'] = 'F';
        $pp_response['reason_text'] = $sberbank->getErrorText();

        fn_finish_payment($order_id, $pp_response);
        fn_order_placement_routines('route', $order_id, false);
    }

}

