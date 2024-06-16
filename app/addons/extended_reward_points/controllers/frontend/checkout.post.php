<?php

use Tygh\Enum\YesNo;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return [CONTROLLER_STATUS_OK];
}

if ($mode == 'cart') {
    $cart = Tygh::$app['view']->getTemplateVars('cart');
    foreach ($cart['products'] as &$product) {
        if (YesNo::toBool($product['is_pbf']) && !empty($product['extra']['points_info']['price'])) unset($product['extra']['points_info']['price']);
    }
    unset($product);

    Tygh::$app['view']->assign('cart', $cart);
}
