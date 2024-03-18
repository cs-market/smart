<?php

use Tygh\Registry;

defined('AREA') or die('Access denied');

function fn_baltika_auth_page_dispatch_assign_template($controller, $mode, $area, $controllers_cascade) {
    $auth_mode = Registry::get('addons.baltika_auth_page.auth_mode');
    if ($controller == 'auth' && $mode == $auth_mode && $auth_mode != 'login_form') {
        Tygh::$app['view']->assign('content_tpl', 'addons/baltika_auth_page/views/auth/baltika_login_form.tpl');
    }
}
