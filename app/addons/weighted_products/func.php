<?php

use Tygh\Registry;

if ( !defined('AREA') ) { die('Access denied'); }

function fn_weighted_products_pre_add_to_cart(&$product_data, $cart, $auth, $update) {
    foreach ($product_data as $key => &$data) {
        if (isset($data['amount_dec'])) {
            $data['amount'] = floatval($data['amount_int'] . '.' . $data['amount_dec']);
            unset($product_data[$key]['amount_dec']);
        }
    }
}

function fn_weighted_products_get_product_fields(&$fields) {
    $fields[] = array('name' => '[data][is_weighted]', 'text' => __('is_weighted'), 'type' => 'C');
}
