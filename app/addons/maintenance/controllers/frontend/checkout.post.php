<?php

$cart = &Tygh::$app['session']['cart'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'update_qty') {

        if (!empty($_REQUEST['product_data'])) {
            $cart_products = array_column($cart['products'], 'item_id', 'product_id');
            $update = [];
            foreach ($_REQUEST['product_data'] as $p_id => $data) {
                $key = $cart_products[$p_id] ?? $p_id;
                $update[$key] = $data;
            }

            $res = fn_add_product_to_cart($update, $cart, $auth, true);
            fn_save_cart_content($cart, $auth['user_id']);
        }

        unset($cart['product_groups']);

        // Recalculate cart when updating the products
        if (!empty($cart['chosen_shipping'])) {
            $cart['calculate_shipping'] = true;
        }
        $cart['recalculate'] = true;

        return [CONTROLLER_STATUS_OK, 'checkout.' . $_REQUEST['redirect_mode']];
    }
}
