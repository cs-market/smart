<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'export') {
        $return_id = $_REQUEST['return_id'];
        if (fn_return_export_to_file($return_id)) {
            fn_set_notification('N', __("notice"), __("text_exim_data_exported"));       
        }
    }
    if ($mode == 'get_file') {
        if (fn_get_return_file($_REQUEST['return_id']) == false) {
            return array(CONTROLLER_STATUS_DENIED);
        }
    }
    if ($mode == 'update_status' && isset($_REQUEST['status']) && isset($_REQUEST['return_id'])) {
        if ($res = db_query('UPDATE ?:returns SET status = ?s WHERE return_id = ?i', $_REQUEST['status'], $_REQUEST['return_id'])) {
            fn_return_export_to_file($_REQUEST['return_id']);
            fn_set_notification('N', __('notice'), __('return_was_approved'));
            return [CONTROLLER_STATUS_REDIRECT, $_REQUEST['return_url']];
        }
    }

    return [CONTROLLER_STATUS_REDIRECT, 'returns.manage'];
} 

if ($mode == 'manage') {
    $params = $_REQUEST;
    $params['get_stats'] = true;
    list($returns, $search) = fn_get_returns($params, Registry::get('settings.Appearance.admin_elements_per_page'));

    Tygh::$app['view']->assign('search', $search);
    Tygh::$app['view']->assign('returns', $returns);
}
