<?php

use Tygh\Registry;
use Tygh\Enum\YesNo;

defined('BOOTSTRAP') or die('Access denied');

if (AREA == 'C' && empty($auth['user_id'])) {
    fn_redirect('auth.login_form');
}

if ($mode == 'view') {
    $promotion_id = empty($_REQUEST['promotion_id']) ? 0 : $_REQUEST['promotion_id'];
    if ($promotion_id) {
        $promotion_data = fn_get_promotion_data($promotion_id);
        Tygh::$app['view']->assign('promotion_data', $promotion_data);
        if (!empty($promotion_data['products'])) {
            $s_params = $_REQUEST;
            $s_params['extend'] = ['categories', 'description'];
            $s_params['pid'] = explode(',', $promotion_data['products']);
            list($products, $search) = fn_get_products($s_params, Registry::get('settings.Appearance.products_per_page'), CART_LANGUAGE);

            fn_gather_additional_products_data($products, array(
                'get_icon' => true,
                'get_detailed' => true,
                'get_additional' => true,
                'get_options' => true,
                'get_discounts' => true,
                'get_features' => false
            ));

            // emulate discount
            if (YesNo::toBool($promotion_data['view_separate'])) {
                $bonuses = array_column($promotion_data['bonuses'], 'bonus');
                if (!array_intersect(['free_products', 'promotion_step_free_products', 'promotion_step_give_condition_products'], $bonuses)) {
                    $backup_amount = [];
                    foreach ($products as $key => &$product) {
                        $backup_amount[$key]['amount'] = $product['amount'];
                        $product['amount'] = 9999;
                    }

                    fn_promotion_apply_bonuses($promotion_data, $tmp, Tygh::$app['session']['auth'], $products);
                    $products = fn_array_merge($products, $backup_amount);
                }
            }

            Tygh::$app['view']->assign('products', $products);
            Tygh::$app['view']->assign('search', $search);

            $selected_layout = fn_get_products_layout($_REQUEST);
            Tygh::$app['view']->assign('selected_layout', $selected_layout);
        }
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
