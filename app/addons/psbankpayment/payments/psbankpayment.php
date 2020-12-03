<?php

use Tygh\Payments\Processors\PSBankLib;

if (defined('PAYMENT_NOTIFICATION')) {

    $order_id = 0;
    if (!empty($_REQUEST['ORDER'])) {
        $order_id = intval($_REQUEST['ORDER']);
    } elseif(!empty($_REQUEST['order_id'])) {
        $order_id = intval($_REQUEST['order_id']);
    }
    if (empty($processor_data)) {
        $order_info = fn_get_order_info($order_id);
        $processor_data = fn_get_processor_data($order_info['payment_id']);
    }

    if ($mode == 'notify') {
        $sign = PSBankLib::calcHash($_POST, $processor_data['processor_params']['key']);
        if(strcmp($sign, $_POST['P_SIGN']) === 0){
            fn_update_psbankpayment_transaction($_POST);
            fn_insert_psbankpayment_history($_POST);
            if(($_POST['TRTYPE'] == 1 || $_POST['TRTYPE'] == 12 || $_POST['TRTYPE'] == 21) && $_POST['RESULT'] == 0) {
                $pp_response = array();
                if($_POST['TRTYPE'] == 12) {
                    $pp_response['order_status'] = $processor_data['processor_params']['status_preauth'];
                } else {
                    $pp_response['order_status'] = $processor_data['processor_params']['status_success'];
                }
                $pp_response['reason_text'] = __('transaction_approved');
                $pp_response['transaction_id'] = $_REQUEST['INT_REF'];
                fn_finish_payment($order_id, $pp_response);
                fn_change_order_status($order_id, $pp_response['order_status'], '',false);

            }
        }
    } elseif ($mode == 'backref')  {
        if (fn_pp_get_order_status($order_info) == STATUS_INCOMPLETED_ORDER) {
            //fn_change_order_status($_REQUEST['order_id'], 'O', '');
            $pp_response['order_status'] = 'N';
            $pp_response['reason_text'] = __('text_transaction_cancelled');
        } else {
             $pp_response['order_status'] = $processor_data['processor_params']['status_success'];
        }

        fn_finish_payment($order_id, $pp_response, false);
        fn_order_placement_routines('route', $order_id, false);
    }
    exit;

} else {

    $payment_url = PSBankLib::getUrl($processor_data['processor_params']['test']);

    $amount = $order_info['total'];
    $return_url = fn_url("payment_notification.backref?payment=psbankpayment&order_id=$order_id", AREA, 'current');

    $date = new DateTime('now', new DateTimeZone('Europe/Moscow'));
    $date->modify('-3Hours');
    $timestamp = $date->format('YmdHis');

    $nonce = sha1(mt_rand().microtime());

    $post_data = array(
        'AMOUNT' => $amount,
        'CURRENCY' => 'RUB',
        'ORDER' => sprintf("%06s", $order_id),
        'MERCH_NAME' => $processor_data['processor_params']['merch_name'],
        'MERCHANT' => $processor_data['processor_params']['merchant'] ,
        'TERMINAL' => $processor_data['processor_params']['terminal'] ,
        'EMAIL' => $order_info['email'],
        'TRTYPE' => $processor_data['processor_params']['trtype'],
        'TIMESTAMP' => $timestamp,
        'NONCE' => $nonce,
        'BACKREF' => $return_url,
        'DESC'  => '#'.$order_id
    );

    $post_data['P_SIGN'] = PSBankLib::calcHash($post_data, $processor_data['processor_params']['key']);
    if ($processor_data['processor_params']['notify']) {
        $post_data['CARDHOLDER_NOTIFY'] = 'EMAIL';
    }
    fn_insert_psbankpayment_transaction($order_id, $post_data ['AMOUNT'], $post_data ['EMAIL'], 0, $post_data ['NONCE']);

    fn_create_payment_form($payment_url, $post_data, 'Промсвязьбанк', false);
}

exit;
