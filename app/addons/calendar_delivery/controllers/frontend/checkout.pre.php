<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$cart = &Tygh::$app['session']['cart'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_REQUEST['delivery_date']) && is_array($_REQUEST['delivery_date'])) {
        foreach($_REQUEST['delivery_date'] as $group_id => $delivery_date) {
            $cart['delivery_date'][$group_id] = $delivery_date;
        }
        foreach ($_REQUEST['delivery_period'] as $group_id => $period) {
            $cart['delivery_period'][$group_id] = $period;
        }
    }
    if (!empty($_REQUEST['documents_originals']) && is_array($_REQUEST['documents_originals'])) {
        $cart['documents_originals'] = $_REQUEST['documents_originals'];
    }
    if ($mode == 'customer_info') {
        if (isset($_REQUEST['delivery_date'])) {
            foreach ($_REQUEST['delivery_date'] as $group_id => $date) {
                $cart['product_groups'][$group_id]['delivery_date'] = $date;
            }
        }
    }
}
