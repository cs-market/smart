<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if (Registry::ifGet('runtime.shop_id', 0) == 2 && empty($auth['user_id'])) {
    $_SESSION['custom_registration'] = 45;
}

$auth_mode = Registry::get('addons.baltika_auth_page.auth_mode');

if ($auth_mode != 'login_form' && $mode == 'login_form' && Registry::ifGet('runtime.shop_id', 0) == 2 && fn_allowed_for('MULTIVENDOR')) {
    return [CONTROLLER_STATUS_REDIRECT, "auth.$auth_mode"];
}
