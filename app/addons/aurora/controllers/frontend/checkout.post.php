<?php
/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*      Copyright (c) 2013 CS-Market Ltd. All rights reserved.             *
*                                                                         *
*  This is commercial software, only users who have purchased a valid     *
*  license and accept to the terms of the License Agreement can install   *
*  and use this program.                                                  *
*                                                                         *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*  PLEASE READ THE FULL TEXT OF THE SOFTWARE LICENSE AGREEMENT IN THE     *
*  "license agreement.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.  *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * **/

defined('BOOTSTRAP') or die('Access denied');

$cart = &Tygh::$app['session']['cart'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'update_qty') {
        if (!empty($_REQUEST['product_data'])) {
            $cart_products = array_column($cart['products'], 'item_id', 'product_id');
            $save = false;
            foreach ($_REQUEST['product_data'] as $p_id => $data) {
                $update = [];
                $cart_id = $cart_products[$p_id] ?? $p_id;
                if (!empty($data['amount'])) {
                    $update[$cart_id] = $data;
                    fn_add_product_to_cart($update, $cart, $auth, true);
                    $save = true;
                } else {
                    fn_delete_cart_product($cart, $cart_id);
                    $save = true;
                }
            }

            if ($save) fn_save_cart_content($cart, $auth['user_id']);
        }

        unset($cart['product_groups']);

        // Recalculate cart when updating the products
        if (!empty($cart['chosen_shipping'])) {
            $cart['calculate_shipping'] = true;
        }
        $cart['recalculate'] = true;

        return [CONTROLLER_STATUS_OK];
    }
}
