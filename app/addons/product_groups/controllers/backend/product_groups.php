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

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'update') {
        $suffix = '.manage';
        fn_update_product_groups($_REQUEST['product_group_data'], $_REQUEST['group_id']);
    }
    if ($mode == 'delete') {
        $suffix = '.manage';
        fn_delete_product_group($_REQUEST['group_id']);
    }
    return array(CONTROLLER_STATUS_OK, "product_groups$suffix");
} 

if ($mode == 'manage') {
    $params = $_REQUEST;
    $product_groups = fn_get_product_groups($params);
    Tygh::$app['view']->assign('product_groups', $product_groups);
} elseif ($mode == 'update') {
    $params = $_REQUEST;
    $product_group = fn_get_product_groups($params);
    Tygh::$app['view']->assign('product_group', reset($product_group));
} elseif ($mode == 'get_groups_list') {

    $params = $_REQUEST;
    $condition = '';
    $pattern = !empty($params['pattern']) ? $params['pattern'] : '';
    $start = !empty($params['start']) ? $params['start'] : 0;
    $limit = (!empty($params['limit']) ? $params['limit'] : 10) + 1;
    if (isset($params['status']) && !empty($params['status'])) {
        $condition .= db_quote(" AND status = ?s", $params['status']);
    }
    if (Registry::get('runtime.company_id')) {
        $condition .= db_quote(" AND company_id = ?i", Registry::get('runtime.company_id'));
    }

    $groups = db_get_hash_array("SELECT ?:product_groups.group_id as value, ?:product_groups.group as name FROM ?:product_groups WHERE 1 ?p AND ?:product_groups.group LIKE ?l ORDER BY ?:product_groups.group LIMIT ?i, ?i", 'value', $condition, $pattern . '%', $start, $limit);
    if (!$start) {
        array_unshift($groups, array('value' => 0, 'name' => '-' . __('none') . '-'));
    }

    if (defined('AJAX_REQUEST') && sizeof($groups) < $limit) {
        Tygh::$app['ajax']->assign('completed', true);
    } else {
        array_pop($groups);
    }

    Tygh::$app['view']->assign('objects', $groups);
    Tygh::$app['view']->assign('id', $params['result_ids']);
    Tygh::$app['view']->display('common/ajax_select_object.tpl');
    exit;
}
