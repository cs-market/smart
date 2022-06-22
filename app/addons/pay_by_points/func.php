<?php

use Tygh\Enum\YesNo;
use Tygh\Enum\SiteArea;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

//  [HOOKs]
function fn_pay_by_points_pre_add_to_cart(&$product_data, &$cart, $auth, $update)
{
    if (SiteArea::isStorefront(AREA)) {
        foreach ($product_data as $key => $data) {
            $product_ids[] = (!empty($data['product_id'])) ? intval($data['product_id']) : intval($key);
        }

        $pay_by_points_data = db_get_hash_array('SELECT is_pbp, points_eq_price, product_id FROM ?:products WHERE product_id IN (?a)', 'product_id', $product_ids);

        foreach($product_data as $key => &$product) {
            $product_id = (!empty($product['product_id'])) ? intval($product['product_id']) : intval($key);

            $product = array_merge($product, $pay_by_points_data[$product_id]);
            $product['point_price'] = (YesNo::toBool($product['is_pbp']))
                    ? (
                            (YesNo::toBool($product['points_eq_price']))
                            ? $product['price']
                            : fn_get_price_in_points($product_id, $auth)
                    )
                    : 0;

            list($product['pay_by_points'], $product['point_price']) = fn_check_product_pay_by_points($product);
            $product['extra']['pay_by_points']['point_price'] = $product['point_price'];
        }

        unset($product);
        fn_update_use_pay_by_points($cart, $product_ids);
    }
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
    fn_update_use_pay_by_points($cart, [$product_id]);

        $points_eq_price = (!isset($product_data['points_eq_price']))
                ? db_get_field("SELECT points_eq_price FROM ?:products WHERE product_id = ?i", $product_id)
                : $product_data['points_eq_price'];

        $reward_point_product_price = (AREA == 'C' && $points_eq_price == 'Y') 
                ? $price 
                : fn_get_price_in_points($product_id, $auth);

        $available_points = fn_get_available_points($cart);
        $product_cart_point_price = $amount * $reward_point_product_price;

        if ($product_cart_point_price > $available_points) {
                //  decrease amount or disallow add to cart
                $new_amount = floor($available_points / $reward_point_product_price);
                $min_qty = db_get_field("SELECT min_qty FROM ?:products WHERE product_id = ?i", $product_id);
                $min_qty = $min_qty ? $min_qty : 0;
                if ($new_amount > $min_qty) {
                        fn_set_notification('N', __('notice'), __(
                                'pay_by_points__notification__change_amount',
                                [
                                '%product%' => fn_get_product_name($product_id),
                                '%old_amount%' => $amount,
                                '%new_amount%' => $new_amount,
                                ]
                        ));
                        $amount = $new_amount;
                        $product_cart_point_price = $amount * $reward_point_product_price;
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
        isset($product['extra']['pay_by_points']['allowed_bonus_pay'])
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
        && isset($product['extra']['pay_by_points']['use_bonus_pay'])
        && $product['extra']['pay_by_points']['use_bonus_pay']
    ) {
        $_pdata['price'] = 0;
    }
}

function fn_pay_by_points_post_add_to_cart($product_data, &$cart, $auth, $update, $ids)
{
    fn_update_use_pay_by_points($cart);
}

function fn_pay_by_points_save_cart_content_pre(&$cart, $user_id, $type, $user_type)
{
    fn_update_use_pay_by_points($cart);
}

function fn_pay_by_points_pre_place_order(&$cart, $allow, &$product_groups)
{
    //  separete orders
    // foreach($product_groups as $group_id => $group) {
    //     $new_group = $bonus_products = [];

    //     foreach($group['products'] as $cart_id => $product) {
    //         if (
    //             isset($product['extra']['pay_by_points']['use_bonus_pay'])
    //             && $product['extra']['pay_by_points']['use_bonus_pay']
    //         ) {
    //             $bonus_products[$cart_id] = $product;
    //             unset($product_groups[$group_id]['products'][$cart_id]);
    //         }
    //     }

    //     //  clone group for separate order isset bonus products
    //     if ($bonus_products) {
    //         $new_group = $group;
    //         $new_group['products'] = $bonus_products;

    //         $product_groups[] = $new_group;
    //     }
    //     fn_set_hook('pay_by_points_divide_cart', $cart, $product_groups, $group_id, $new_group);
    // }

    //  push bonus reward data for the reward_points add-on
    //  (if order will separated => main order delete => put on all with point)
        // $total_bonus = 0;
        // foreach ($cart['products'] as $product) {
        //     if (
        //         isset($product['extra']['pay_by_points']['product_cart_point_price'])
        //     ) {
        //         $total_bonus += $product['extra']['pay_by_points']['product_cart_point_price'];
        //     }
        // }
    
        // if ($total_bonus) {
        //     $cart['points_info']['reward'] = $total_bonus;
        // }

        // earned_points_eq_price
        // $reward = 0;
        // foreach ($cart['products'] as &$product) {
        //     $eq_price = false;

        //     // check in the product
        //     $eq_price = (db_get_field("SELECT earned_points_eq_price FROM ?:products WHERE product_id = ?i", $product['product_id']) == 'Y');

        //     // check in categories
        //     if (!$eq_price) {
        //         $category_ids = fn_get_category_ids_with_parent($product['category_ids']);
        //         $eq_price = (bool) db_get_field("SELECT COUNT(category_id) FROM ?:categories WHERE category_id IN (?n) AND earned_points_eq_price = ?s", $category_ids, 'Y');
        //     }


        //     if ($eq_price) {
        //         $product['extra']['points_info']['reward'] = $product['price'];
        //         $bonus_price = $product['amount'] * $product['price'];
        //     } else {
        //         $bonus_price = $product['extra']['points_info']['reward'] ?: '0';
        //     }

        //     $reward += $bonus_price;
        // }

        // $cart['points_info']['reward'] = $reward;
        // /earned_points_eq_price
}

function fn_pay_by_points_reward_points_calculate_item(&$cart_products, $cart, $k, $v) {
    $eq_price = false;
        // check in the product
        $eq_price = (db_get_field("SELECT earned_points_eq_price FROM ?:products WHERE product_id = ?i", $v['product_id']) == 'Y');

        // check in categories
        if (!$eq_price) {
                $category_ids = fn_get_category_ids_with_parent($v['category_ids']);
                $eq_price = (bool) db_get_field("SELECT COUNT(category_id) FROM ?:categories WHERE category_id IN (?n) AND earned_points_eq_price = ?s", $category_ids, 'Y');
        }

        if ($eq_price) {
            $cart_products[$k]['points_info']['reward']['raw_amount'] = $v['price'];
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
    if (!empty($order)) {
        $total_bonus = 0;

        foreach ($order['products'] as $product) {
            if (
                isset($product['extra']['pay_by_points']['product_cart_point_price'])
            ) {
                $total_bonus += $product['extra']['pay_by_points']['product_cart_point_price'];
            }
        }
        if ($total_bonus) $order['points_info']['in_use']['points'] = $total_bonus;
        $order['total_bonus'] = $total_bonus;
    }
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

function fn_pay_by_points_calculate_cart_post(&$cart, $auth, $calculate_shipping, $calculate_taxes, $options_style, $apply_cart_promotions, $cart_products, $product_groups)
{
    $cart['pay_by_points']['reward'] = fn_get_use_pay_by_points($cart);
}

function fn_pay_by_points_load_products_extra_data(&$extra_fields, $products, $product_ids, $params, $lang_code)
{
    $user_groups = ($params['area'] == 'A')
    ? USERGROUP_ALL
    : array_unique(array_merge(array(USERGROUP_ALL), Tygh::$app['session']['auth']['usergroup_ids']));

    $extra_fields['?:product_point_prices'] = [
        'primary_key' => 'product_id',
        'fields' => [
            'point_price' => 'MIN(point_price)'
        ],
        'condition' => db_quote(' AND ?:product_point_prices.lower_limit = 1 AND ?:product_point_prices.usergroup_id IN (?n)',
        $user_groups
        ),
        'group_by' => 'GROUP BY ?:product_point_prices.product_id'
    ];

}

function fn_pay_by_points_get_products_post(&$products, $params, $lang_code)
{
        foreach($products as &$product) {
                list($product['pay_by_points'], $product['point_price']) = fn_check_product_pay_by_points($product);
        }
        unset($product);
}

function fn_pay_by_points_gather_additional_product_data_before_discounts(&$product, $auth, $params) {
        list($product['pay_by_points'], $product['point_price']) = fn_check_product_pay_by_points($product);
        if ($product['point_price']) {
                $product['points_info']['price'] = $product['point_price'];
        }
}
//  [/HOOKs]

/*
* Get availeble point
* get from session
* return float
*/
function fn_get_available_points($cart)
{
    return max(
        fn_get_user_additional_data(POINTS) - $cart['pay_by_points']['in_use'],
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
function fn_update_use_pay_by_points(&$cart, $disallow_products = [])
{
    $cart['pay_by_points']['in_use'] = fn_get_use_pay_by_points($cart, $disallow_products);
}

function fn_get_use_pay_by_points(&$cart, $disallow_products = [])
{
    $total_use_points = 0;
    if (!empty($cart['products']))
    foreach ($cart['products'] as &$product) {
        if (
            !in_array($product['product_id'], $disallow_products)
            // && isset($product['extra']['pay_by_points']['product_cart_point_price'])
            // && $product['extra']['pay_by_points']['product_cart_point_price']
        ) {
            $product['extra']['pay_by_points']['product_cart_point_price'] = $product['extra']['pay_by_points']['point_price'] * $product['amount'];

            $total_use_points += $product['extra']['pay_by_points']['product_cart_point_price'];
        }
    }

    return $total_use_points;
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

/*
* Get pay_by_points and correct bonus price
*
* $product array
* return [pay_by_points, point_price]
*/
function fn_check_product_pay_by_points($product)
{
    $is_pbp = $product['is_pbp'] ?? 'N';
    if ($is_pbp != 'Y') {
        return ['N', 0];
    }

    $points_eq_price = (!isset($product['points_eq_price']))
        ? db_get_field("SELECT points_eq_price FROM ?:products WHERE product_id = ?i", $product['product_id'])
        : $product['points_eq_price'];

    if (AREA == 'C' && $points_eq_price == 'Y') {
        if (!isset($product['price']) || !$product['price']) {
            $product['price'] = fn_get_product_price(
                $product['product_id'],
                $product['amount'] ?? 1,
                $_SESSION['auth']
            );
        }
        $point_price = round($product['price'], 2);
    } else {
        if (!isset($product['point_price']) || !$product['point_price']) {
            $product['point_price'] = fn_get_price_in_points($product['product_id'], Tygh::$app['session']['auth']);
        }
        $point_price = $product['point_price'];
    }

    return [
        $point_price ? 'Y' : 'N',
        $point_price
    ];
}
