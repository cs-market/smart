<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return [CONTROLLER_STATUS_OK];
}

if ($mode == 'cart') {
    if ($storages = Registry::get('runtime.storages')) {
        $cart_products = Tygh::$app['view']->getTemplateVars('cart_products');

        $product_groups = Tygh::$app['view']->getTemplateVars('product_groups');
        foreach ($product_groups as &$group) {
            $group['total'] = $group['display_subtotal'] = $group['package_info_full']['C'];
            $group['weight'] = $group['package_info_full']['W'];
            foreach($group['products'] as $cart_id => &$product) {
                $product['display_subtotal'] = $cart_products[$cart_id]['display_subtotal'];
                if (!empty($cart_products[$cart_id]['box_contains'])) $product['box_contains'] = $cart_products[$cart_id]['box_contains'];
            }
        }
        Tygh::$app['view']->assign('product_groups', $product_groups);
    }
}
