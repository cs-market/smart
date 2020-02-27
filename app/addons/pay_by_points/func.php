<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

//  [HOOKs]
function fn_pay_by_points_pre_add_to_cart($product_data, $cart, $auth, $update)
{
  $product_ids = fn_array_column($product_data, 'product_id');
  fn_update_use_pay_by_points($product_ids);
}

function fn_pay_by_points_check_add_to_cart_post($cart, $product, $product_id, &$result)
{
  if (!isset($product['pay_by_points']) || $product['pay_by_points'] != 'Y') {
    return;
  }

  $is_pbp = db_get_field("SELECT is_pbp FROM ?:products WHERE product_id = ?i", $product_id);

  // if pay by points unavailable
  if ($is_pbp != 'Y') {
    fn_set_notification('E', __('error'), __(
      'pay_by_points__notification__pbp_unavailable',
      ['%product%' => fn_get_product_name($product['product_id'])]
    ));
    $result = false;
  }
}

function fn_pay_by_points_add_product_to_cart_get_price($product_data, &$cart, $auth, $update, $_id, &$data, $product_id, &$amount, $price, $zero_price_action, &$allow_add)
{
  if (!isset($data['pay_by_points']) || $data['pay_by_points'] != 'Y') {
    return;
  }

  $data['extra']['pay_by_points']['allowed_bonus_pay'] = true;

  if (!$update && isset($cart['products'][$_id])) {
    $amount += $cart['products'][$_id]['amount'];
    fn_delete_cart_product($cart, $_id);
  }
  fn_update_use_pay_by_points([$product_id]);

  $available_points = fn_get_available_points();
  $product_cart_point_price = $amount * $price;

  if ($product_cart_point_price > $available_points) {
    //  decrease amount or disallow add to cart
    $new_amount = floor($available_points / $price);

    if ($new_amount > 0) {
      fn_set_notification('N', __('notice'), __(
        'pay_by_points__notification__change_amount',
        [
          '%product%' => fn_get_product_name($product_id),
          '%old_amount%' => $amount,
          '%new_amount%' => $new_amount,
        ]
      ));
      $amount = $new_amount;
      $product_cart_point_price = $amount * $price;
    } else {
      fn_set_notification('E', __('error'), __(
        'pay_by_points__notification__not_enough_points',
        ['%product%' => fn_get_product_name($product_id)]
      ));
      fn_delete_cart_product($cart, $_id);
      $allow_add = false;
    }
  }

  $data['extra']['pay_by_points']['product_cart_point_price'] = $product_cart_point_price;
}

function fn_pay_by_points_add_to_cart(&$cart, $product_id, $_id)
{
  $product = &$cart['products'][$_id];

  if (
    isset($product['extra']['pay_by_points'])
    && $product['extra']['pay_by_points']['allowed_bonus_pay']
  ) {
    $product['extra']['pay_by_points']['use_bonus_pay'] = true;
    $product['price'] = 0;
  } else {
    $product['extra']['pay_by_points']['use_bonus_pay'] = false;
  }
}

function fn_pay_by_points_get_cart_product_data($product_id, &$_pdata, $product, $auth, $cart, $hash)
{
  if (
    $product['price'] == 0
    && $product['extra']['pay_by_points']['use_bonus_pay']
  ) {
    $_pdata['price'] = 0;
  }
}

function fn_pay_by_points_post_add_to_cart($product_data, $cart, $auth, $update, $ids)
{
  fn_update_use_pay_by_points();
}

function fn_pay_by_points_save_cart_content_pre($cart, $user_id, $type, $user_type)
{
  fn_update_use_pay_by_points();
}

function fn_pay_by_points_pre_place_order(&$cart, $allow, &$product_groups)
{
  //  separete orders
  foreach($product_groups as $group_id => $group) {

    $bonus_products = [];

    foreach($group['products'] as $cart_id => $product) {
      if (
        isset($product['extra']['pay_by_points']['use_bonus_pay'])
        && $product['extra']['pay_by_points']['use_bonus_pay']
      ) {
        $bonus_products[$cart_id] = $product;
        unset($product_groups[$group_id]['products'][$cart_id]);
      }
    }

    //  clone group for separate order isset bonus products
    if ($bonus_products) {
      $new_group = $group;
      $new_group['products'] = $bonus_products;
      $product_groups[] = $new_group;
    }
  }

  //  push bonus reward data for the reward_points add-on
  //  (if order will separated => main order delete => put on all with point)
  $total_bonus = 0;
  foreach ($cart['products'] as $product) {
    if (
      isset($product['extra']['pay_by_points']['product_cart_point_price'])
    ) {
      $total_bonus += $product['extra']['pay_by_points']['product_cart_point_price'];
    }
  }

  if ($total_bonus) {
    $cart['points_info']['reward'] = $total_bonus;
  }
}

function fn_pay_by_points_get_orders_post($params, &$orders)
{
  foreach ($orders as &$order) {
    $order['total_bonus'] = fn_get_order_total_bonus($order['order_id']);
  }
  unset($order);
}

function fn_pay_by_points_get_order_info(&$order, $additional_data)
{
  $total_bonus = 0;

  foreach ($order['products'] as $product) {
    if (
      isset($product['extra']['pay_by_points']['product_cart_point_price'])
    ) {
      $total_bonus += $product['extra']['pay_by_points']['product_cart_point_price'];
    }
  }

  $order['total_bonus'] = $total_bonus;
}

function fn_pay_by_points_change_order_status($status_to, $status_from, &$order_info, $force_notification, $order_statuses, $place_order)
{
  $total_bonus = 0;

  foreach ($order_info['products'] as $product) {
    if (
      isset($product['extra']['pay_by_points']['product_cart_point_price'])
    ) {
      $total_bonus += $product['extra']['pay_by_points']['product_cart_point_price'];
    }
  }

  $order_info['points_info']['in_use']['points'] = $total_bonus;
}
//  [/HOOKs]

/*
 * Get avvaileble point
 * get from session
 * return float
 */
function fn_get_available_points()
{
  return max(
    fn_get_user_additional_data(POINTS) - Tygh::$app['session']['cart']['pay_by_points']['in_use'],
    0
  );
}

/*
 * Update point info
 * unclude product list
 * use session
 * $disallow_products array product_ids
 * return void
 */
function fn_update_use_pay_by_points($disallow_products = [])
{
  $total_use_points = 0;
  foreach (Tygh::$app['session']['cart']['products'] as $product) {
    if (
      !in_array($product['product_id'], $disallow_products)
      && isset($product['extra']['pay_by_points']['product_cart_point_price'])
      && $product['extra']['pay_by_points']['product_cart_point_price']
    ) {
      $total_use_points += $product['extra']['pay_by_points']['product_cart_point_price'];
    }
  }
  Tygh::$app['session']['cart']['pay_by_points']['in_use'] = $total_use_points;
}

/*
 * Get order total bonus info
 *
 * $order_id Int
 * return $total_bonus String
 */
function fn_get_order_total_bonus($order_id)
{
  $total_bonus = "0";
  $datas = db_get_fields("SELECT extra FROM ?:order_details WHERE order_id = ?i", $order_id);

  foreach ($datas as $data) {
    $data = unserialize($data);

    if (isset($data['pay_by_points']['product_cart_point_price'])) {
      $total_bonus += $data['pay_by_points']['product_cart_point_price'];
    }
  }

  return (String) $total_bonus;
}
