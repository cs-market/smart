<?php

use Tygh\Registry;
use Tygh\Enum\YesNo;

function fn_get_1c_code($oid) {
    $oi = fn_get_order_info($oid);
    return (empty($oi['fields']['39'])) ? $oi['fields']['38'] : $oi['fields']['39'];
}

function fn_get_payment_name($payment_id, $lang_code = CART_LANGUAGE) {
    if (!empty($payment_id)) {
        return db_get_field("SELECT payment FROM ?:payment_descriptions WHERE payment_id = ?i AND lang_code = ?s", $payment_id, $lang_code);
    }

    return false;
}

function fn_import_change_order_status($object) {
    if (isset($object['order_id'], $object['status']) && !empty($object['order_id']) && !empty($object['status'])) {
        $order_id = $object['order_id'];
        // we need to change reward point in advance
        if ($object['total'] && Registry::get('addons.reward_points.status') == 'A' && !empty(db_get_field("SELECT data FROM ?:order_data WHERE order_id = ?i AND type = ?s", $order_id, POINTS))) {
            $order_info = fn_get_order_info($order_id);
            $db_points = $order_info['points_info']['reward'];
            $_data = db_get_row("SELECT ?:users.user_id, user_login as login FROM ?:users LEFT JOIN ?:orders ON ?:orders.user_id = ?:users.user_id WHERE order_id = ?i", $order_id);
            fn_define('ORDER_MANAGEMENT', true);
            $customer_auth = fn_fill_auth($_data, array(), false, 'C');
            fn_clear_cart($cart);

            fn_form_cart($order_id, $cart, $customer_auth);

            unset($cart['points_info']);
            $cart['subtotal'] = $object['total'];
            fn_promotion_apply('cart', $cart, $customer_auth, $products);
            if (isset($cart['points_info']['additional'])) {
                $reward = $cart['points_info']['additional'];
                $order_data = array(
                    'order_id' => $order_id,
                    'type' => POINTS,
                    'data' => $reward
                );
                db_query("REPLACE INTO ?:order_data ?e", $order_data);

                // if is gain - correct value
                if (YesNo::toBool($order_info['points_info']['is_gain'])) {
                    fn_change_user_points($reward-$db_points, $order_info['user_id'], "Корректировка баллов по заказу #$order_id: $db_points —> $reward", CHANGE_DUE_ADDITION);
                }
            }
        }

        fn_change_order_status($object['order_id'], $object['status']);
    }
}

function fn_get_total_history($sorting, $order_id) {
    $total = db_get_field("SELECT description FROM ?:order_logs as logs "
        . " LEFT JOIN ?:users as users USING(user_id) WHERE logs.order_id = ?i AND action = 'rus_order_logs_order_total' ORDER BY logs.log_id $sorting", $order_id
    );

    return $total;
}

function fn_exim_get_order_unique_sku($order_id) {
    return db_get_field('SELECT count(DISTINCT(product_id)) FROM ?:order_details WHERE order_id = ?i', $order_id);
}
