<?php

use Tygh\Registry;
use Tygh\Enum\RewardPointsMechanics;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return ;
}

if ($mode == 'update') {
    $product = Tygh::$app['view']->getTemplateVars('product_data');
    if (RewardPointsMechanics::isPartialPayment($product['reward_points_mechanics'])) {
        Tygh::$app['view']->assign('is_partial_reward_points', true);
        fn_get_product_min_prices($product['product_id'], $product, $auth);
        Tygh::$app['view']->assign('product_data', $product);

        Registry::set('navigation.tabs.min_prices', array (
            'title' => __('extended_reward_points.min_prices'),
            'js' => true
        ));
    }
}
