<?php
use Tygh\Payments\Processors\PSBankLib;
if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_psbankpayment_install()
{
    fn_psbankpayment_uninstall();

    $_data = array(
        'processor' => 'Промсвязьбанк: Интернет-Эквайринг',
        'processor_script' => 'psbankpayment.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'psbankpayment.tpl',
        'callback' => 'Y',
        'type' => 'P',
        'addon' => 'psbankpayment'
    );

    db_query("INSERT INTO ?:payment_processors ?e", $_data);
}

function fn_psbankpayment_uninstall()
{
    db_query("DELETE FROM ?:payment_processors WHERE processor_script = ?s", "psbankpayment.php");
}

function fn_update_psbankpayment_transaction($data) {
    $fields = array("AUTHCODE","CARD","EMAIL","INT_REF","NAME","RC","RCTEXT","RESULT","RRN","TRTYPE", "NONCE");
    foreach($fields as $field) {
        if(isset($data[$field])  && $data[$field] !== ''){
            $query[] = db_quote("{$field}=?s", $data[$field]);
        }
    }
    if($data['RESULT'] == 0) {
        $query[] = db_quote("STATUS=?i",$data['TRTYPE']);
        if ($data['TRTYPE'] == 22 || $data['TRTYPE'] == 14){
            $query[] = db_quote("AMOUNT=AMOUNT-?s",$data['AMOUNT']);
        } else {
            $query[] = db_quote("AMOUNT=?s", $data['AMOUNT']);
        }
    }
    db_query("UPDATE ?:psbankpayment SET
        ".implode(',', $query).",
        DATE=NOW()
        WHERE ORDER_ID=?i", $data['ORDER']
    );
}

function fn_insert_psbankpayment_transaction ($order_id, $amount, $email, $trtype, $nonce) {
    db_query("INSERT INTO ?:psbankpayment (ORDER_ID, AMOUNT, ORG_AMOUNT,EMAIL,DATE, TRTYPE, NONCE)
    VALUES(?i, ?s, ?s, ?s, NOW(), ?i, ?s) ON DUPLICATE KEY UPDATE AMOUNT=VALUES(AMOUNT),ORG_AMOUNT=VALUES(ORG_AMOUNT),EMAIL=VALUES(EMAIL),DATE=NOW(),TRTYPE=VALUES(TRTYPE),NONCE=VALUES(NONCE)", $order_id, $amount, $amount, $email, $trtype, $nonce);
}

function fn_insert_psbankpayment_history ($data) {
    db_query("INSERT INTO ?:psbankpayment_history  (AMOUNT, ORG_AMOUNT, CURRENCY, `ORDER`, `DESC`, MERCH_NAME, MERCHANT, TERMINAL, EMAIL,TRTYPE,`TIMESTAMP`,NONCE,RESULT, RC, RCTEXT, AUTHCODE, RRN, INT_REF, NAME, CARD, CHANNEL) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s)",$data['AMOUNT'],$data['ORG_AMOUNT'],$data['CURRENCY'],$data['ORDER'],$data['DESC'],$data['MERCH_NAME'],$data['MERCHANT'],$data['TERMINAL'],$data['EMAIL'],$data['TRTYPE'],$data['TIMESTAMP'],$data['NONCE'],$data['RESULT'],$data['RC'],$data['RCTEXT'],$data['AUTHCODE'],$data['RRN'],$data['INT_REF'],$data['NAME'],$data['CARD'],$data['CHANNEL']);
}

function fn_psbankpayment_send_request($post){
    $order_info = fn_get_order_info($post['order_id']);
    $processor_data = fn_get_processor_data($order_info['payment_id']);
    $date = new DateTime('now', new DateTimeZone('Europe/Moscow'));
    $date->modify('-3Hours');
    $timestamp = $date->format('YmdHis');
    $data = db_get_array('SELECT * FROM ?:psbankpayment WHERE ORDER_ID=?i', $post['order_id']);
    $data = $data[0];
    $nonce = sha1(mt_rand().microtime());
    $params = array(
        "ORDER"			=> sprintf("%06s", $post['order_id']),
        "AMOUNT"		=> $post['sum'],
        "CURRENCY"		=> "RUB",
        "ORG_AMOUNT"	=> $data['ORG_AMOUNT'],
        "RRN"			=> $data['RRN'],
        "INT_REF"		=> $data['INT_REF'],
        "TRTYPE"		=> $post['trtype'],
        "TERMINAL"		=> $processor_data['processor_params']['terminal'],
        "BACKREF"		=> fn_url("payment_notification.backref?payment=psbankpayment&order_id=${post['order_id']}", AREA, 'current'),
        "EMAIL"			=> $data['EMAIL'],
        "TIMESTAMP"		=> $timestamp,
        "NONCE"			=> $nonce
    );
    $params['P_SIGN'] = PSBankLib::calcHash($params, $processor_data['processor_params']['key']);
    if($processor_data['processor_params']['notify']) {
        $params['CARDHOLDER_NOTIFY'] = 'EMAIL';
    }
    $res = PSBankLib::request($params, $processor_data['processor_params']['test']);
    return $nonce;
}
