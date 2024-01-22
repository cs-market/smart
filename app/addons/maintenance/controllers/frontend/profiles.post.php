<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'update') {
    if (Registry::get('settings.General.allow_usergroup_signup') != 'Y') {
        Registry::del('navigation.tabs.usergroups');
        Tygh::$app['view']->assign('usergroups', array());
    }
}
