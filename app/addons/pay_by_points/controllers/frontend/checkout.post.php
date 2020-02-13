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
  // 'E'
  fn_print_r('s');
  fn_print_die("z");
  // FIXME: mb call calculate cart if it !call auto
  $cart['recalculate'] = true;
$product_price = $cart['total'];
unset($cart);
  $points_to_use = empty($_REQUEST['points_to_use']) ? 0 : intval($_REQUEST['points_to_use']);
  if (!empty($points_to_use) && abs($points_to_use) == $points_to_use) {
    Tygh::$app['session']['cart']['points_info']['in_use']['points'] = $points_to_use;
  }

  $redirect_mode = isset($_REQUEST['redirect_mode']) ? $_REQUEST['redirect_mode'] : 'checkout';

  return array(CONTROLLER_STATUS_REDIRECT, 'checkout.' . $redirect_mode . '.show_payment_options');
}
