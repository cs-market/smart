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
        fn_update_storage($_REQUEST['storage_data'], $_REQUEST['storage_id']);
    }
    if ($mode == 'delete') {
        $suffix = '.manage';
        fn_delete_storages($_REQUEST['storage_id']);
    }
    return array(CONTROLLER_STATUS_OK, "storages$suffix");
} 

if ($mode == 'manage') {
    $params = $_REQUEST;
    list($storages, $search) = fn_get_storages($params, Registry::get('settings.Appearance.admin_elements_per_page'));

    Tygh::$app['view']->assign('storages', $storages);
    Tygh::$app['view']->assign('search', $search);
} elseif ($mode == 'update') {
    $params = $_REQUEST;
    list($storage, ) = fn_get_storages($params);
    Tygh::$app['view']->assign('storage', reset($storage));
    if (defined('AJAX_REQUEST')) {
        Tygh::$app['view']->assign('in_popup', true);
    }
}
