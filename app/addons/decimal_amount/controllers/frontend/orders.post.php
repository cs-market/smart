<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'reorder') {
    $cart = &Tygh::$app['session']['cart'];
    unset($cart['backup_product_data'], $cart['qty_step_backup'], $cart['amount_backup'], $cart['amount_product_data'], $cart['current_amount']);
    fn_calculate_cart_content($cart, $auth);
    fn_save_cart_content($cart, $auth['user_id']);

}
