<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'view') {
    $promotion_id = empty($_REQUEST['promotion_id']) ? 0 : $_REQUEST['promotion_id'];
    if ($promotion_id) {
        $promotion_data = fn_get_promotion_data($promotion_id);
        fn_print_die($promotion_data);
    } else {
        return array(CONTROLLER_STATUS_DENIED);
    }
} elseif ($mode == 'list') {
    $promotions = Tygh::$app['view']->getTemplateVars('promotions');
    $simple_promotions = array_filter($promotions, function($v) {
        return $v['view_separate'] == 'N';
    });
    $promotions = array_filter($promotions, function($v) {
        return $v['view_separate'] == 'Y';
    });
    $data = fn_array_column($simple_promotions, 'products');
    $data = array_filter($data);
    $product_ids = array_unique(explode(',', implode(',', $data)));
    if ($product_ids) {
        $params = $_REQUEST;
        $params['extend'] = ['categories', 'description'];
        $params['pid'] = $product_ids;
        list($products, $search) = fn_get_products($params, Registry::get('settings.Appearance.products_per_page'), CART_LANGUAGE);

        fn_gather_additional_products_data($products, array(
            'get_icon' => true,
            'get_detailed' => true,
            'get_additional' => true,
            'get_options' => true,
            'get_discounts' => true,
            'get_features' => false
        ));

        $selected_layout = fn_get_products_layout($_REQUEST);
        Tygh::$app['view']->assign('show_qty', true);
        Tygh::$app['view']->assign('products', $products);
        Tygh::$app['view']->assign('search', $search);
        Tygh::$app['view']->assign('selected_layout', $selected_layout);

        Tygh::$app['view']->assign('promotions', $promotions);
    }
}
