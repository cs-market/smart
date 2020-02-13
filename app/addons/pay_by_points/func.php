<?php

// use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

//  [HOOKs]
function fn_pay_by_points_pre_add_to_cart(&$product_data, $cart, $auth, &$update)
{
  foreach ($product_data as $cart_id => &$product) {
    if (!isset($product['pay_by_points']) || !$product['pay_by_points']) {
      continue;
    }

    list($product_cart_point_price, $product_update) = fn_get_pre_order_bonus_product_price($cart['products'], $cart_id, $product);
    $product['extra']['pay_by_points']['product_cart_point_price'] = $product_cart_point_price;
    $product['extra']['pay_by_points']['allowed_bonus_pay'] = true;

    //  remove old payed by bonus data
    if ($product_update) {
      fn_change_total_in_use_bonus($product, false);
    }

    // just one is needed for all to be updated
    $update = $update || $product_update;
  }
  unset($product);
}

function fn_pay_by_points_check_add_to_cart_post($cart, $product, $product_id, &$result)
{
  if (!isset($product['pay_by_points']) || !$product['pay_by_points']) {
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
    return;
  }

  // set on pre_add_to_cart hook
  $product_cart_point_price = $product['extra']['pay_by_points']['product_cart_point_price'] ?? 0;

  if (!$product_cart_point_price) {
    $result = false;
    return;
  }

  if ($product_cart_point_price > $cart['user_data']['points']) {
    fn_set_notification('E', __('error'), __(
      'pay_by_points__notification__not_enough_points',
      ['%product%' => fn_get_product_name($product['product_id'])]
    ));
    $result = false;
    return;
  }
}

function fn_pay_by_points_add_to_cart(&$cart, $product_id, $_id)
{
  $product = &$cart['products'][$_id];

  if (
    isset($product['extra']['pay_by_points'])
    && $product['extra']['pay_by_points']['allowed_bonus_pay']
  ) {
    fn_change_total_in_use_bonus($product);
    $product['price'] = 0;
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
//  [/HOOKs]

/*
 * If exist in cart, user cart amount
 * otherwise use new product data
 * $cart_products Array of products from session cart
 * $cart_id Int add to cart data id
 * $product Array add to cart data
 * Return [bounus_price, update_status]
 */
function fn_get_pre_order_bonus_product_price($cart_products, $cart_id, $product)
{
  $update = isset($cart_products[$cart_id]);
  $pr = $update ? $cart_products[$cart_id] : $product;

  return [
    $pr['amount'] * $pr['base_price'],
    $update
  ];
}

/*
 * Increase or decrease total use bounus data
 * and change use_bonus_pay product data value
 * $product Array
 * $increase Bool increase | decrease
 */
function fn_change_total_in_use_bonus(&$product, $increase = true)
{
  $product['extra']['pay_by_points']['use_bonus_pay'] = $increase ? true : false;

  $bonus = ($increase ? 1 : -1) *  $product['extra']['pay_by_points']['product_cart_point_price'];

  Tygh::$app['session']['cart']['points_info']['in_use']['points'] = max(
    Tygh::$app['session']['cart']['points_info']['in_use']['points'] + $bonus,
    0
  );
}
