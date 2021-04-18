<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_trusted_vars (
    'template_data'
);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'update') {
        $template_data = $_REQUEST['template_data'];
        $template_id = fn_update_template($template_data, $_REQUEST['template_id']);
    }
    if ($mode == 'delete') {
        if (isset($_REQUEST['template_id'])) {
            fn_delete_message_template($_REQUEST['template_id']);
        }
    }
    return array(CONTROLLER_STATUS_OK, 'message_templates.manage');
}

if ($mode == 'manage') {
    $params = $_REQUEST;

    $templates = fn_get_message_templates($params);

    Tygh::$app['view']->assign('templates', $templates);
} elseif ($mode == 'update') {
    $params = $_REQUEST;

    $templates = fn_get_message_templates($params);

    if (!empty($templates)) {
        $template = array_shift(array_shift($templates));
    }

    Tygh::$app['view']->assign('template', $template);
}
