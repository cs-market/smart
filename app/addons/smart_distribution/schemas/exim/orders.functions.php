<?php

function fn_get_1c_code($oid) {
    $oi = fn_get_order_info($oid);
    return (empty($oi['fields']['38'])) ? '' : $oi['fields']['38'];
}

function fn_get_payment_name($payment_id, $lang_code = CART_LANGUAGE) {
    if (!empty($payment_id)) {
        return db_get_field("SELECT payment FROM ?:payment_descriptions WHERE payment_id = ?i AND lang_code = ?s", $payment_id, $lang_code);
    }

    return false;
}

function fn_import_change_order_status($object) {
    if (isset($object['order_id'], $object['status']) && !empty($object['order_id']) && !empty($object['status']))
    fn_change_order_status($object['order_id'], $object['status']);
}
