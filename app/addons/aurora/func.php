<?php

use Tygh\Enum\YesNo;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_get_mobile_app_links() {
    if ($company_id = Tygh::$app['session']['auth']['company_id']) {
        $links = db_get_row('SELECT app_store, play_market, app_gallery FROM ?:companies WHERE company_id = ?i', $company_id);
        return array_filter($links);
    }
}

function fn_blocks_aurora_get_vendor_info() {
    $company_id = !empty(Tygh::$app['session']['auth']['company_id']) ? Tygh::$app['session']['auth']['company_id'] : null;

    $company_data = [];
    $company_data['logos'] = fn_get_logos($company_id);

    if (!is_file($company_data['logos']['theme']['image']['absolute_path'])) {
        $company_data['logos'] = fn_get_logos(null);
    }

    return $company_data;
}

function fn_aurora_get_product_data_post(&$product_data, $auth, $preview, $lang_code) {
    $cart_products = array_column(Tygh::$app['session']['cart']['products'], 'amount', 'product_id');
    $product_data['dynamic_quantity'] = Registry::get('addons.aurora.dynamic_quantity');

    if (YesNo::toBool($product_data['dynamic_quantity']) && $product_data['in_cart'] = !empty($cart_products[$product_data['product_id']])) {
        $product_data['selected_amount'] = $cart_products[$product_data['product_id']];
    }
}

function fn_aurora_load_products_extra_data_post(&$products, $product_ids, $params, $lang_code) {
    foreach ($products as $product_id => $product) {
        $products[$product_id]['dynamic_quantity'] = Registry::get('addons.aurora.dynamic_quantity');
    }
}

function fn_aurora_get_products_post(&$products, $params, $lang_code) {
    $cart_products = array_column(Tygh::$app['session']['cart']['products'], 'amount', 'product_id');
    foreach ($products as &$product_data) {
        if (YesNo::toBool($product_data['dynamic_quantity'])) {
            if ($product_data['in_cart'] = !empty($cart_products[$product_data['product_id']])) {
                $product_data['selected_amount'] = $cart_products[$product_data['product_id']];
            }
        }
    }
}
