<?php

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'add') {
        $notifications = &Tygh::$app['session']['notifications'];

        if (!empty($notifications)) {
            foreach ($notifications as $key => $data) {
                if ($data['message'] == __('product_in_wishlist')) {
                    unset($notifications[$key]);
                    $wishlist = & Tygh::$app['session']['wishlist'];
                    foreach ($_REQUEST['product_data'] as $product_id => $data) {
                        if (!empty($data['product_id'])) {
                            $product_id = $data['product_id'];
                        }
                        $wishlist['products'] = array_filter($wishlist['products'], function($v) use ($product_id) {
                            return $v['product_id'] != $product_id;
                        });
                    }

                    fn_save_cart_content($wishlist, $auth['user_id'], 'W');
                }
            }
        }
    }
}
