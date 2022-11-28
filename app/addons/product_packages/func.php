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

function fn_product_packages_exim_1c_import_value_fields(&$product, $value_field, $_name_field, $_v_field) {
    if ($_name_field == 'КоличествоШтукВКоробке') {
        $product['items_in_package'] = $_v_field;
    }
}

function fn_product_packages_exim_1c_import_features_definition(&$features_import, $feature_name, $_feature, $cml) {
    if (in_array($feature_name, ['Количество в коробке'])) {
        $features_import['items_in_package']['id'] = strval($_feature -> {$cml['id']});
        $features_import['items_in_package']['name'] = 'items_in_package';
    }
}

function fn_product_packages_exim_1c_import_features_values(&$product, $_feature, $features_commerceml, $cml) {
    if (!empty($features_commerceml['items_in_package']['id']) && $features_commerceml['items_in_package']['id'] == $_feature -> {$cml['id']}) {
        $product['items_in_package'] = intval($_feature -> {$cml['value']});
    }
}
