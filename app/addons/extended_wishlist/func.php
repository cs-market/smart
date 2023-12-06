<?php

use Tygh\Registry;
use Tygh\Enum\YesNo;

defined('BOOTSTRAP') or die('Access denied');

function fn_extended_wishlist_add_product_to_wishlist($product_data, &$wishlist, &$auth) {
    if (Registry::get('addons.wishlist.status') == 'A') {
        if (!is_callable('fn_add_product_to_wishlist')) {
            include_once Registry::get('config.dir.addons').'wishlist/controllers/frontend/wishlist.php';
        }
        return fn_add_product_to_wishlist($product_data, $wishlist, $auth);
    }

    return false;
}

function fn_extended_wishlist_create_order($order) {
    if (!defined('ORDER_MANAGEMENT')) {
        if (fn_allowed_for('MULTIVENDOR')) {
            $allow = db_get_field('SELECT add_order_to_wl FROM ?:companies WHERE company_id = ?i', $order['company_id']);
        } else {
            $allow = Registry::get('addons.extended_wishlist.add_order_to_wl');
        }

        if (YesNo::toBool($allow)) {
            $wishlist = & Tygh::$app['session']['wishlist'];
            if ($order['user_id'] == Tygh::$app['session']['auth']['user_id']) {
                $auth = Tygh::$app['session']['auth'];
            } else {
                $auth = fn_fill_auth($order['user_id'], array(), false, 'C');
            }

            $product_data = array();
            foreach ($order['products'] as $product) {
                $product_data[$product['product_id']] = array(
                    'product_id' => $product['product_id'],
                    'amount' => $product['amount']
                );
            }

            $product_ids = fn_extended_wishlist_add_product_to_wishlist($product_data, $wishlist, $auth);

            fn_save_cart_content($wishlist, $auth['user_id'], 'W');
        }
    }
}

function fn_extended_wishlist_user_logout_before_clear_cart($auth, $clear_cart) {
    if ($clear_cart) {
        fn_clear_cart(Tygh::$app['session']['wishlist'], false, true);
    }
}
