<?php

defined('BOOTSTRAP') or die('Access denied');

$tmp_cart = Tygh::$app['session']['cart'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return [CONTROLLER_STATUS_OK];
}
if ($mode == 'checkout') {
    fn_product_groups_pre_update_order($tmp_cart);

    foreach ($tmp_cart['product_groups'] as $key => &$product_group) {
        if ($product_group['group']['min_order'] && $product_group['group']['min_order'] > $product_group['subtotal']) {
            $formatter = Tygh::$app['formatter'];
            $min_amount = $formatter->asPrice($product_group['group']['min_order']);
            fn_set_notification(
                'W',
                __('notice'),
                __('checkout.min_cart_subtotal_required', [
                    '[amount]' => $min_amount,
                    '[group]' => $product_group['group']['group'],
                ])
            );

            return [CONTROLLER_STATUS_REDIRECT, 'checkout.cart'];
        }
    }
}