<?php

use Tygh\Enum\UserRoles;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return array(CONTROLLER_STATUS_OK);
}

if ($mode == 'update') {
    $user_data = Tygh::$app['view']->getTemplateVars('user_data');
    if ($user_data['user_role'] == UserRoles::trading_network()) {
        list($network_users) = fn_get_users(['network_id' => $user_data['user_id']], $auth);
        Tygh::$app['view']->assign('network_users', array_column($network_users, 'user_id'));
    }
}
