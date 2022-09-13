<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'login_form' && Registry::ifGet('runtime.shop_id', 0) == 2 && fn_allowed_for('MULTIVENDOR')) {
    return [CONTROLLER_STATUS_REDIRECT, 'auth.baltika_login_form'];
}
