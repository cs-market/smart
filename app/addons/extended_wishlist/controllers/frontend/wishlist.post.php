<?php
/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*      Copyright (c) 2013 CS-Market Ltd. All rights reserved.             *
*                                                                         *
*  This is commercial software, only users who have purchased a valid     *
*  license and accept to the terms of the License Agreement can install   *
*  and use this program.                                                  *
*                                                                         *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*  PLEASE READ THE FULL TEXT OF THE SOFTWARE LICENSE AGREEMENT IN THE     *
*  "license agreement.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.  *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * **/

use Tygh\Registry;
use Tygh\Enum\YesNo;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'add') {
        if (YesNo::toBool(Registry::get('addons.extended_wishlist.remove_wl_notifications'))) {
            foreach (Tygh::$app['session']['notifications'] as $k => $v) {
                if ($v['type'] == 'I') {
                    unset(Tygh::$app['session']['notifications'][$k]);
                }
            }
        }
    }

    return [CONTROLLER_STATUS_OK];
}

if ($mode == 'view') {
    $products = Tygh::$app['view']->getTemplateVars('products');

    $categories_products = fn_group_array_by_key($products, 'main_category');
    $category_ids = array_keys($categories_products);

    list($categories) = fn_get_categories(['item_ids' => implode(',', $category_ids), 'group_by_level' => false, 'simple' => false]);

    $categories = fn_array_value_to_key($categories, 'category_id');

    Tygh::$app['view']->assign('categories', $categories);
    Tygh::$app['view']->assign('categories_products', $categories_products);
}
