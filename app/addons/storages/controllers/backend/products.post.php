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

// use Tygh\Addons\Warehouses\ServiceProvider;
// use Tygh\Enum\YesNo;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return [CONTROLLER_STATUS_OK];
}

if ($mode == 'update') {
    $company_id = null;

    $product_data = Tygh::$app['view']->getTemplateVars('product_data');
    $product_company_id = isset($_REQUEST['product_data']['company_id'])
        ? (int) $_REQUEST['product_data']['company_id']
        : (int) $product_data['company_id'];

    $runtime_company_id = (int) Registry::get('runtime.company_id');

    if (fn_allowed_for('MULTIVENDOR')) {
        $company_id = $product_company_id;
    }

    /** @var Tygh\Addons\Warehouses\Manager $manager */
    // $manager = Tygh::$app['addons.warehouses.manager'];

    /** @var Tygh\Addons\Warehouses\ProductStock $product_stock */
    //$product_stock = $manager->getProductWarehousesStock($_REQUEST['product_id']);

    //$warehouses = $manager->getWarehouses($company_id);
    list($storages) = fn_get_storages(['company_id' => $product_company_id]);
    $storages_amount = fn_get_storages_amount($_REQUEST['product_id']);

    Tygh::$app['view']->assign([
        'storages'         => $storages,
        'storages_amounts' => $storages_amount
    ]);

    if ($storages) {
        Registry::set('navigation.tabs.storages', [
            'title' => __('storages.storages'),
            'js' => true,
        ]);
    }

    // if (empty($warehouses) && !$product_stock->hasStockSplitByWarehouses()) {
    //     return [CONTROLLER_STATUS_OK];
    // }

    // if (
    //     fn_allowed_for('ULTIMATE')
    //     && !$product_stock->hasStockSplitByWarehouses()
    //     && $runtime_company_id
    //     && $runtime_company_id !== $product_company_id
    // ) {
    //     return [CONTROLLER_STATUS_OK];
    // }

    // if ($product_stock->hasStockSplitByWarehouses()) {
    //     $product_warehouses_amount = $product_stock->getAmount();
    //     Tygh::$app['view']->assign('product_warehouses_amount', $product_warehouses_amount);
    // }



    // // Quantity tab is not avaliable if product is common or about to be.
    // $is_quantity_tab_avaliable = !isset($company_id) || !empty($company_id);

}

if ($mode === 'm_update') {
    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];
    /** @var array $products_data */
    $products_data = $view->getTemplateVars('products_data');
    $readonly_fields = [];

    /** @var Tygh\Addons\Warehouses\Manager $manager */
    $manager = Tygh::$app['addons.warehouses.manager'];

    foreach ($products_data as $product) {
        $product_id = $product['product_id'];
        $readonly_fields[$product_id]['amount'] = YesNo::toBool($product['is_stock_split_by_warehouses']);
    }

    $view->assign('readonly_fields', $readonly_fields);
}
