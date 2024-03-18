<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'login_form' || $mode == Registry::get('addons.baltika_auth_page.auth_mode')) {
    if (isset($_SESSION['custom_registration'])) {
        $schema = fn_get_schema('user_from_form', 'schema');
        $company_id = $_SESSION['custom_registration'] ;
        $pages = array_filter($schema, function($v, $k) use ($company_id) {
            return $v['company_id'] == $company_id;
        }, ARRAY_FILTER_USE_BOTH);
        if ($pages) {
            list($pages, ) = fn_get_pages(array('item_ids' => implode(',', array_keys($pages)), 'status' => ['A', 'H']));
            Tygh::$app['view']->assign('registration_pages', $pages);
        }
    }
}
