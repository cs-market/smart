<?php

use Tygh\Registry;
use Tygh\Enum\YesNo;

defined('AREA') or die('Access denied');

if ($mode == 'product_catalog') {
    if (!empty($auth['user_id'])) return array(CONTROLLER_STATUS_NO_PAGE);

    $params = $_REQUEST;
    if (fn_allowed_for('MULTIVENDOR') && !isset($_REQUEST['company_id'])) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }
    fn_add_breadcrumb(__('catalog'));
    $params['custom_extend'] = ['product_name', 'categories'];
    $params['exclude_cid'] = 9064;
    list($products, $search) = fn_get_products($params, Registry::get('settings.Appearance.products_per_page'), CART_LANGUAGE);
    fn_gather_additional_products_data($products, array(
        'get_icon' => true,
        'get_detailed' => true,
        'get_additional' => true,
        'get_options' => false,
        'get_discounts' => false,
        'get_features' => false
    ));
    array_walk($products, function(&$p) {
        $p['package_switcher'] = YesNo::NO;
        $p['zero_price_action'] = 'P';
    });

    if (isset($search['page']) && ($search['page'] > 1) && empty($products)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    Tygh::$app['view']->assign('products', $products);
    Tygh::$app['view']->assign('search', $search);
}
