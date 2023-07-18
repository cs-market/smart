<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

function fn_order_product_prices_add_product_to_cart_get_price($product_data, $cart, $auth, $update, $_id, &$data, $product_id, $amount, $price, $zero_price_action, $allow_add) {
    if (empty($data['extra']['initial_price'])) $data['extra']['initial_price'] = db_get_field('SELECT price FROM ?:product_prices WHERE product_id = ?i AND usergroup_id = ?i', $product_id, 0);
}
