<?php

use Tygh\Registry;

defined('AREA') or die('Access denied');

if ($mode == 'product_catalog') {
    if (!empty($auth['user_id'])) return array(CONTROLLER_STATUS_NO_PAGE);

    $params = $_REQUEST;
    if (fn_allowed_for('MULTIVENDOR') && !isset($_REQUEST['company_id'])) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    $params['custom_extend'] = ['product_name'];
    list($products, $search) = fn_get_products($params, Registry::get('settings.Appearance.products_per_page'), CART_LANGUAGE);

    if (isset($search['page']) && ($search['page'] > 1) && empty($products)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    Tygh::$app['view']->assign('products', $products);
    Tygh::$app['view']->assign('search', $search);
}
