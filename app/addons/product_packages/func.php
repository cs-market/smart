<?php

use Tygh\Registry;
use Tygh\Enum\SiteArea;

if ( !defined('AREA') ) { die('Access denied'); }

function fn_product_packages_pre_get_cart_product_data($hash, $product, $skip_promotion, $cart, $auth, $promotion_amount, &$fields, $join, $params) {
    $fields[] = '?:products.items_in_package';
}

function fn_product_packages_get_product_data($product_id, &$field_list, $join, $auth, $lang_code, $condition, $price_usergroup) {
    if (SiteArea::isStorefront(AREA) && fn_allowed_for('MULTIVENDOR')) {
        $field_list .= ", companies.package_switcher";
    }
}

function fn_product_packages_load_products_extra_data(&$extra_fields, $products, $product_ids, $params, $lang_code) {
    if (SiteArea::isStorefront(AREA) && fn_allowed_for('MULTIVENDOR')) {
        $extra_fields['?:companies'] = [
            'primary_key' => 'product_id',
            'fields' => [
                'package_switcher',
                'product_id' => '?:products.product_id'
            ],
            'join' => db_quote(' LEFT JOIN ?:products ON ?:products.company_id = ?:companies.company_id')
        ];
    }
}

function fn_product_packages_pre_add_to_cart(&$product_data) {
    foreach ($product_data as $key => &$product) {
        if (isset($product['shop_by_packages']) && !empty($product['shop_by_packages']) && !empty($product['amount'])) {
            $product['amount'] *= $product['shop_by_packages'];
            unset($product['shop_by_packages']);
        }
    }
}
