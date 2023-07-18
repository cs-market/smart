<?php

use Tygh\Registry;
use Tygh\Storage;
use Tygh\Enum\YesNo;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'cancel' || $mode == 'edit') {
        $params = $_REQUEST;
        if (isset($params['order_id'])) {
            $order = fn_get_order_info($params['order_id']);
            if (!empty($order)) {
                $status_data = fn_get_status_params($order['status'], STATUSES_ORDER);
                if (!empty($status_data) && YesNo::toBool($status_data['allow_cancel'])) {
                    fn_change_order_status($order['order_id'], Registry::get('addons.order_cancellation.cancellation_status'));
                    if ($mode == 'cancel') {
                        fn_redirect(fn_url('orders.details&order_id='.$order['order_id']));
                    } elseif ($mode == 'edit') {
                        fn_order_cancellation_reorder($order['order_id'], Tygh::$app['session']['cart'], $auth);
                        fn_redirect(fn_url('checkout.cart'));
                    }
                }
            }
        }
    }

    return array(CONTROLLER_STATUS_OK);
}

function fn_order_cancellation_reorder($order_id, &$cart, &$auth)
{
    $order_info = fn_get_order_info($order_id, false, false, false, true);
    unset(Tygh::$app['session']['shipping_hash']);
    unset(Tygh::$app['session']['edit_step']);

    fn_set_hook('reorder', $order_info, $cart, $auth);

    foreach ($order_info['products'] as $k => $item) {
        // refresh company id
        $company_id = db_get_field("SELECT company_id FROM ?:products WHERE product_id = ?i", $item['product_id']);
        $order_info['products'][$k]['company_id'] = $company_id;

        unset($order_info['products'][$k]['extra']['ekey_info']);
        unset($order_info['products'][$k]['extra']['promotions']);
        unset($order_info['products'][$k]['promotions']);

        $order_info['products'][$k]['product_options'] = empty($order_info['products'][$k]['extra']['product_options']) ? array() : $order_info['products'][$k]['extra']['product_options'];
        $order_info['products'][$k]['main_pair'] = fn_get_cart_product_icon($item['product_id'], $order_info['products'][$k]);
    }

    if (!empty($cart) && !empty($cart['products'])) {
        $cart['products'] = fn_array_merge($cart['products'], $order_info['products']);
    } else {
        $cart['products'] = $order_info['products'];
    }

    foreach ($cart['products'] as $k => $product) {
        $_is_edp = db_get_field("SELECT is_edp FROM ?:products WHERE product_id = ?i", $product['product_id']);
        if ($amount = fn_check_amount_in_stock($product['product_id'], $product['amount'], $product['product_options'], $k, $_is_edp, 0, $cart)) {
            $cart['products'][$k]['amount'] = $amount;

            // Check if the product price with options modifiers equals to zero
            $price = fn_get_product_price($product['product_id'], $amount, $auth);
            $zero_price_action = db_get_field('SELECT zero_price_action FROM ?:products WHERE product_id = ?i', $product['product_id']);
            $zero_price_action = fn_normalize_product_overridable_field_value('zero_price_action', $zero_price_action);

            /**
             * Executed for each product when an order is re-ordered.
             * Allows you to modify the data of a product in the order.
             *
             * @param array     $order_info         Order info from fn_get_order_info()
             * @param array     $cart               Array of cart content and user information necessary for purchase
             * @param array     $auth               Array of user authentication data (e.g. uid, usergroup_ids, etc.)
             * @param array     $product            Product data
             * @param int       $amount             Product quantity
             * @param float     $price              Product price
             * @param string    $zero_price_action  Flag, determines the action when the price of the product is 0
             * @param string    $k                  Product cart ID
             */
            fn_set_hook('reorder_product', $order_info, $cart, $auth, $product, $amount, $price, $zero_price_action, $k);

            if (!(float) $price && $zero_price_action === ProductZeroPriceActions::ASK_TO_ENTER_PRICE) {
                if (isset($product['custom_user_price'])) {
                    $price = $product['custom_user_price'];
                }
            }

            $price = fn_apply_options_modifiers($product['product_options'], $price, 'P', array(), array('product_data' => $product));

            if (!floatval($price)) {
                $data['price'] = isset($data['price']) ? fn_parse_price($data['price']) : 0;

                if (AREA == 'C'
                    && ($zero_price_action == 'R'
                        ||
                        ($zero_price_action == 'A' && floatval($data['price']) < 0)
                    )
                ) {
                    if ($zero_price_action == 'A') {
                        fn_set_notification('E', __('error'), __('incorrect_price_warning'));
                    } else {
                        fn_set_notification('W', __('warning'), __('warning_zero_price_restricted_product', array(
                            '[product]' => $product['product']
                        )));
                    }

                    unset($cart['products'][$k]);

                    continue;
                }
            }

            // Change the path of custom files
            if (!empty($product['extra']['custom_files'])) {
                foreach ($product['extra']['custom_files'] as $option_id => $_data) {
                    if (!empty($_data)) {
                        foreach ($_data as $file_id => $file) {
                            $cart['products'][$k]['extra']['custom_files'][$option_id][$file_id]['path'] = 'sess_data/' . fn_basename($file['path']);
                        }
                    }
                }
            }
        } else {
            unset($cart['products'][$k]);
        }
    }

    // Restore custom files for editing
    $dir_path = 'order_data/' . $order_id;

    if (Storage::instance('custom_files')->isExist($dir_path)) {
        Storage::instance('custom_files')->copy($dir_path, 'sess_data');
    }

    // Redirect customer to step three after reordering
    $cart['payment_updated'] = true;

    fn_save_cart_content($cart, $auth['user_id']);
    unset($cart['product_groups']);
}
