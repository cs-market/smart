<?php

use Tygh\Enum\UserTypes;
use Tygh\Enum\YesNo;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return [CONTROLLER_STATUS_OK];
}

if ($mode == 'update') {
    $tg_allowed = true;
    $user_data = Tygh::$app['view']->getTemplateVars('user_data');
    if (fn_allowed_for('MULTIVENDOR') && !empty($user_data['company_id'])) {
        $tg_allowed = YesNo::toBool(db_get_field('SELECT tg_enabled FROM ?:companies WHERE company_id = ?i', $user_data['company_id']));
    }
    Tygh::$app['view']->assign('tg_allowed', $tg_allowed);
}
