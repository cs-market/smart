<?php

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

    return [CONTROLLER_STATUS_REDIRECT, 'returns.manage'];
} 

if ($mode == 'manage') {
    $params = $_REQUEST;
    list($returns, $search) = fn_get_returns($params);

    Tygh::$app['view']->assign('search', $search);
    Tygh::$app['view']->assign('returns', $returns);
}
