<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  return;
}

if ($mode == 'point_payment_product') {
  $cart_id = $_REQUEST['cart_id'] ?? 0;
  $cart = &Tygh::$app['session']['cart'];

  if (!$cart_id || !isset($cart['products'][$cart_id])) {
    fn_set_notification('E', __('error'), 'Cart ID is bad');
    return;
  }

  $product_data = $cart['products'][$cart_id];
  $product_data['pay_by_points'] = true;

  fn_add_product_to_cart([$cart_id => $product_data], $cart, $auth);
  fn_calculate_cart_content($cart, $auth, 'S', true);

  $redirect_mode = isset($_REQUEST['redirect_mode']) ? $_REQUEST['redirect_mode'] : 'cart';

  return array(CONTROLLER_STATUS_REDIRECT, 'checkout.' . $redirect_mode . '.show_payment_options');
}
