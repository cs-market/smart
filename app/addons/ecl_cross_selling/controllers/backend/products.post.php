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

    // Update required products
    if ($mode == 'update') {
        $product_id = !empty($_REQUEST['product_id']) ? $_REQUEST['product_id'] : 0;
        $related_products = !empty($_REQUEST['related_products']) ? $_REQUEST['related_products'] : '';

        if (!empty($product_id) && isset($_REQUEST['related_products'])) {
            fn_ecl_related_products_update($product_id, $related_products);
        }
    }
}

if ($mode == 'update') {
    $product_id = empty($_REQUEST['product_id']) ? 0 : intval($_REQUEST['product_id']);
    $related_products = fn_ecl_get_related_products($product_id);

    Registry::set('navigation.tabs.related_products', array(
        'title' => __('related_products'),
        'js' => true
    ));

    Registry::get('view')->assign('related_products', $related_products);
}
