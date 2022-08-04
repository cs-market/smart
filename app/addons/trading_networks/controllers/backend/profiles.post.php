<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return array(CONTROLLER_STATUS_OK);
}

if ($mode == 'update') {
    $user_data = Tygh::$app['view']->getTemplateVars('user_data');
    list($network_users) = fn_get_users(['network_id' => $user_data['user_id']], $auth);
    Tygh::$app['view']->assign('network_users', $network_users);
}
