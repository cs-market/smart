<?php

use Tygh\Settings;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

$a_name = 'sw_telegram';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_REQUEST['addon'] == $a_name) {
    if ($mode == 'update') {
        fn_sw_telegram_update_addon_status_ckeckin(false, true);
    } elseif ($mode == 'sw_support') {

        fn_trusted_vars('message');
        $params = $_REQUEST;
        $params['addon_settings'] = Registry::get('addons.' . $params['addon']);
        $params['user_id'] = $auth['user_id'];

        fn_sw_telegram_send_support_info($params);

        return array(CONTROLLER_STATUS_OK, $params['return_url']);
    }
}

if (isset($_REQUEST['addon']) && $_REQUEST['addon'] == $a_name) {
    list($support_chat, $search) = fn_sw_telegram_get_support_info_messages($_REQUEST);
    Tygh::$app['view']->assign('support_chat', $support_chat);
    Tygh::$app['view']->assign('search', $search);


    if (defined('AJAX_REQUEST')) {
        list($support_chat, $search) = fn_sw_telegram_get_support_info_messages($_REQUEST);
        Tygh::$app['view']->assign('support_chat', $support_chat);
        Tygh::$app['view']->assign('search', $search);

        $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;

        $_REQUEST['return_url'] = fn_url('addons.update&addon=' . $a_name . '&selected_section=' . $a_name . '_review_tab&page=' . $page);

        return array(CONTROLLER_STATUS_OK, $return_url);
    }
}
