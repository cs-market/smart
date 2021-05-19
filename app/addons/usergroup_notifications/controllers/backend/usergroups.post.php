<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    fn_trusted_vars('usergroup_data');

    if ($mode == 'update') {
        $usergroup_id = $_REQUEST['usergroup_id'] ?? 0;
        if ($usergroup_id) {
            db_query('UPDATE ?:usergroup_descriptions SET email_template = ?s WHERE usergroup_id = ?i AND lang_code = ?s', $_REQUEST['usergroup_data']['email_template'], $usergroup_id, DESCR_SL);
        }
    }

    return array(CONTROLLER_STATUS_OK);
}

if ($mode == 'update') {
    $usergroup_id = isset($_REQUEST['usergroup_id']) ? $_REQUEST['usergroup_id'] : null;

    $tabs = Registry::get('navigation.tabs');

    $tabs['email_template_' . $usergroup_id] = [
        'title' => __('template'),
        'js' => true,
    ];

    Registry::set('navigation.tabs', $tabs);
}
