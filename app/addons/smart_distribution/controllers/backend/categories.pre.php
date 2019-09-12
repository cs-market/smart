<?php

use Tygh\Models\VendorPlan;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return array(CONTROLLER_STATUS_OK);
}


if ($mode == 'search' || $mode == 'picker') {
    $params = $_REQUEST;
    if (!empty($params['company_id'])) {
        $cids = db_get_field("SELECT categories FROM ?:vendor_plans AS vp LEFT JOIN ?:companies AS c ON vp.plan_id = c.plan_id WHERE company_id = ?i", $params['company_id']);
        $params['category_ids'] = explode(',', $cids);
    }
    $params['add_root'] = !empty($_REQUEST['root']) ? $_REQUEST['root'] : '';

    list($categories, $search) = fn_get_categories($params);
    Tygh::$app['view']->assign('categories_tree', $categories);
    Tygh::$app['view']->assign('search', $search);
    if (defined('AJAX_REQUEST')) {
        if (!empty($_REQUEST['random'])) {
            Tygh::$app['view']->assign('random', $_REQUEST['random']);
        }
        Tygh::$app['view']->assign('category_id', $category_id);
    }
    if ($mode == 'picker') {
	    Tygh::$app['view']->display('pickers/categories/picker_contents.tpl');
	    exit;
	}
}