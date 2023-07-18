<?php

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'add_return') {
        $products_data = $_REQUEST['product_data'];
        if (!empty($products_data)) {
            $return_id = fn_create_return($products_data, $auth);
            if ($return_id) {
                fn_set_notification('N', __('notice'), __('return_added_successfully', ['[return_id]' => $return_id]));
            }
        }
        return [CONTROLLER_STATUS_REDIRECT, 'index.index'];
    }

    return;
} 

if ($mode == 'request') {
    list($ordered_products) = fn_get_products(['only_ordered' => true]);

    foreach($ordered_products as &$product) {
        $product['selected_amount'] = 0;
        $product['min_qty'] = 0;
    }

    unset($product);

    Tygh::$app['view']->assign('ordered_products', $ordered_products);
}
