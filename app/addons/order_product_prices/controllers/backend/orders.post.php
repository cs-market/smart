<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'details') {
    $order_info = Tygh::$app['view']->getTemplateVars('order_info');
    $products = array_column($order_info['products'], 'product_id', 'item_id');
    $base_prices = db_get_hash_single_array('SELECT product_id, price FROM ?:product_prices WHERE product_id IN (?a) AND usergroup_id = 0', ['product_id', 'price'], $products);
    foreach($order_info['products'] as &$product) {
        $product['initial_price'] = $base_prices[$product['product_id']];
    }

    Tygh::$app['view']->assign('order_info', $order_info);
    Registry::set('navigation.tabs.prices', array(
        'title' => __('prices'),
        'js' => true
    ));
}
