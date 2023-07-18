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
use Tygh\Addons\SchemesManager;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    fn_trusted_vars('sticker_data');

    if ($mode == 'm_delete') {
        foreach ($_REQUEST['sticker_ids'] as $v) {
            fn_delete_sticker_by_id($v);
        }
        $suffix = '.manage';
    }

    if ($mode == 'update') {
        $sticker_id = fn_update_sticker($_REQUEST['sticker_data'], $_REQUEST['sticker_id'], DESCR_SL);
        if (empty($sticker_id)) {
            $suffix = '.manage';
        } else {
            $suffix = ".update?sticker_id=$sticker_id";
        }
    }
    return array(CONTROLLER_STATUS_OK, "stickers$suffix");
} 

if ($mode == 'manage' || $mode == 'picker') {
    $params = $_REQUEST;
    $stickers = fn_get_stickers($params, DESCR_SL);
    if ($mode == 'picker' && $params['display'] == 'radio') {
        array_unshift($stickers, array('sticker_id' => '', 'name' => $params['root']));
    }

    Registry::get('view')->assign('stickers', $stickers);

    if ($mode == 'picker') {
        Registry::get('view')->display('addons/product_stickers/pickers/stickers/picker_contents.tpl');
        exit;
    }
} elseif ($mode == 'update') {
    $params = $_REQUEST;
    list($sticker_data) = fn_get_stickers($params, DESCR_SL);
    Registry::get('view')->assign('sticker_data', $sticker_data);
    $schema = fn_get_schema('styles', 'properties');
    Registry::get('view')->assign('schema', $schema);
} elseif ($mode == 'add') {
    $schema = fn_get_schema('styles', 'properties');
    Registry::get('view')->assign('sticker_data', array('display' => 'PCB'));
    Registry::get('view')->assign('schema', $schema);
} elseif ($mode == 'delete') {
    if (!empty($_REQUEST['sticker_id'])) {
        fn_delete_sticker_by_id($_REQUEST['sticker_id']);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "stickers.manage");
} elseif ($mode == 'dynamic_style') {
    $params = $_REQUEST;

    $schema = fn_get_schema('styles', 'properties');
    Tygh::$app['view']->assign('name', $params['name']);
    Tygh::$app['view']->assign('elm_id', $params['elm_id']);
    Tygh::$app['view']->assign('property', $schema[$params['property']]);
}
