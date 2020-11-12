<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'update') {
    Registry::set('navigation.tabs.user_price', array (
        'title' => __('user_price'),
        'js' => true
    ));
} elseif ($mode == 'get_user_price') {
    $params = $_REQUEST;

    if (!isset($params['items_per_page'])) {
        $params['items_per_page'] = Registry::get('settings.Appearance.admin_elements_per_page');
    }

    list($user_price, $user_price_search) = fn_get_product_user_price_with_params($params);

    Tygh::$app['view']->assign('user_price', $user_price);
    Tygh::$app['view']->assign('user_price_search', $user_price_search);

    if (defined('AJAX_REQUEST')) {
        Tygh::$app['view']->display('addons/user_price/views/products/get_user_price.tpl');
        exit;
    }
}
