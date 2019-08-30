<?php
/*****************************************************************************
 *                                                                            *
 *                   All rights reserved! eCom Labs LLC                       *
 * http://www.ecom-labs.com/about-us/ecom-labs-modules-license-agreement.html *
 *                                                                            *
 *****************************************************************************/

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'complete') {
    if (Registry::get('addons.ecl_cross_selling.reminder_notification') == 'Y') {
        $order_id = !empty($_REQUEST['order_id']) ? $_REQUEST['order_id'] : 0;

        $product_and_block_data = fn_ecl_get_related_products_for_order($order_id);

        if (!empty($product_and_block_data['products']) && !empty($product_and_block_data['block_data'])) {
            Registry::get('view')->assign('related_products_for_cart', $product_and_block_data['products']);
            Registry::get('view')->assign('block_related_product_data', $product_and_block_data['block_data']);
        }
    }
} elseif ($mode = 'cart') {
    if (Registry::get('addons.ecl_cross_selling.cart_notification') == 'Y' && !empty($_SESSION['cart'])) {
        $cart = & $_SESSION['cart'];
        $product_and_block_data = fn_ecl_get_related_products_for_cart($cart);

        Registry::get('view')->assign('cart_related_product_data', $product_and_block_data);
    }
}
