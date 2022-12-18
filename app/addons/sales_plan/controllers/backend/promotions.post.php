<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'add') {
    $params = $_REQUEST;
    if ($params['user_ids']) {
        $promotion_data['conditions'] = array(
            'set' => 'all',
            'set_value' => 1,
        );
        $promotion_data['conditions']['conditions'][] = array(
            'operator' => 'in',
            'condition' => 'users',
            'value' => $params['user_ids'],
        );  
    }
    if ($params['category_ids']) {
        $promotion_data['bonuses'][] = array(
            'bonus' => 'discount_on_categories',
            'value' => $params['category_ids'],
            'discount_bonus' => 'by_percentage',
            'discount_value' => 0,
        );
    }
    if ($params['product_ids']) {
        $promotion_data['bonuses'][] = array(
            'bonus' => 'discount_on_products',
            'value' => $params['product_ids'],
            'discount_bonus' => 'by_percentage',
            'discount_value' => 0,
        );
    }
    Tygh::$app['view']->assign('promotion_data', $promotion_data);
}
