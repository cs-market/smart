<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'userlog') {
    if (fn_allowed_for('MULTIVENDOR') && $company_id = Registry::get('runtime.company_id')) {
        $user = Tygh::$app['view']->getTemplateVars('user');
        if (empty($user)) {
            Tygh::$app['view']->assign('userlog', []);
            Tygh::$app['view']->assign('search', []);
        }
    }
}
