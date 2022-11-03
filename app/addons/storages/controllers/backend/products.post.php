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

    list($storages) = fn_get_storages(['company_id' => $product_company_id]);
    array_walk($storages, function(&$storage){
        $storage['storage'] .= ' ('. $storage['code'] .')';
    });

    $storages_amount = fn_get_storages_amount($_REQUEST['product_id']);

    foreach ($storages_amount as $storage_id => $data) {
        $storages[$storage_id]['has_value'] = true;
    }

    $storages = fn_sort_array_by_key($storages, 'has_value', SORT_DESC);

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
}
if ($mode == 'get_user_price') {
    list($storages) = fn_get_storages(['company_id' => $_REQUEST['company_id']]);
    Tygh::$app['view']->assign('storages', $storages);
}
