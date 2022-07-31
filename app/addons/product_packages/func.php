<?php

use Tygh\Registry;

if ( !defined('AREA') ) { die('Access denied'); }

function fn_product_packages_pre_get_cart_product_data($hash, $product, $skip_promotion, $cart, $auth, $promotion_amount, &$fields, $join, $params) {
    $fields[] = '?:products.items_in_package';
}
