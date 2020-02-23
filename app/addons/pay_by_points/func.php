<?php

// use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

//  [HOOKs]
function fn_pay_by_points_pre_add_to_cart(&$product_data, $cart, $auth, &$update)
{
  Tygh::$app['session']['cart']['pay_by_points']['in_use'] = 0;
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

  $available_points = fn_get_available_points();
  $product_cart_point_price = $amount * $price;

  if ($product_cart_point_price > $available_points) {
    //  increase amount or disallow add to cart
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
      $allow_add = false;
      return;
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

function fn_pay_by_points_post_add_to_cart($product_data, &$cart, $auth, $update, $ids)
{
  //  update point info
  $total_use_points = 0;
  foreach ($cart['products'] as $product) {
    if (
      isset($product['extra']['pay_by_points'])
      && $product['extra']['pay_by_points']['product_cart_point_price']
    ) {
      $total_use_points += $product['extra']['pay_by_points']['product_cart_point_price'];
    }
  }

  $cart['pay_by_points']['in_use'] = $total_use_points;
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
// function fn_get_pre_order_bonus_product_price($cart_products, $cart_id, $product)
// {
//   $update = isset($cart_products[$cart_id]);
//   $pr = $update ? $cart_products[$cart_id] : $product;
//
//   return [
//     $pr['amount'] * $pr['base_price'],
//     $update
//   ];
// }

// /*
//  * Increase or decrease total use bounus data
//  * and change use_bonus_pay product data value
//  * $product Array
//  * $increase Bool increase | decrease
//  */
// function fn_change_total_in_use_bonus(&$product, $increase = true)
// {
//   $product['extra']['pay_by_points']['use_bonus_pay'] = $increase ? true : false;
//
//   $bonus = ($increase ? 1 : -1) *  $product['extra']['pay_by_points']['product_cart_point_price'];
//   fn_print_r(Tygh::$app['session']['cart']['points_info']);
//   fn_print_die("z");
// // $cart['points_info']['total_price']
//   Tygh::$app['session']['cart']['points_info']['in_use']['points'] = max(
//     Tygh::$app['session']['cart']['points_info']['in_use']['points'] + $bonus,
//     0
//   );
// }

/*
 * Get avvaileble point
 * get from session
 * return float
 */
function fn_get_available_points()
{
  // FIXME: mb add fnc, availble points - points for curent pr
  return max(
    Tygh::$app['session']['cart']['user_data']['points'] - Tygh::$app['session']['cart']['pay_by_points']['in_use'],
    0
  );
}
