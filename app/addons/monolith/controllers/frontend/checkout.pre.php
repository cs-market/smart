<?php

defined('BOOTSTRAP') or die('Access denied');

$cart = &Tygh::$app['session']['cart'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'place_order') {
        if (empty($cart['user_data']['email'])) {
            $cart['user_data']['email'] = fn_checkout_generate_fake_email_address($cart['user_data'], TIME);
        }
    }
}
