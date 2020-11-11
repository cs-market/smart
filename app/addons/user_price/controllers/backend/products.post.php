<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'update') {
    Registry::set('navigation.tabs.user_price', array (
        'title' => __('user_price'),
        'js' => true
    ));

    $user_price = fn_get_product_user_price($_REQUEST['product_id'], null, ['items_per_page' => Registry::get('settings.Appearance.admin_elements_per_page')]);
    Tygh::$app['view']->assign('user_price', $user_price);
    // fn_print_r('efds', $user_price);
    // fn_print_die("z");

}
