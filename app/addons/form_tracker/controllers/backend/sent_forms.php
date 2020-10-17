<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'm_update') {
        $forms_data = $_REQUEST['forms_data'];
        foreach ($forms_data as $submit_id => $form_data) {
            fn_update_submitted_form($form_data, $submit_id);
        }
    }

    if ($mode == 'update') {
        $submit_id = $_REQUEST['submit_id'];
        if (!empty($submit_id)) {
            $submitted_form = array('form_data' => $_REQUEST['form'], 'comments' => $_REQUEST['comments'], 'status' => $_REQUEST['status']);
            $submit_id = fn_update_submitted_form($submitted_form, $submit_id);
        }
    }

    if ($mode == 'm_delete') {
        $submit_ids = $_REQUEST['submit_ids'];
        if (!empty($submit_ids)) {
            foreach ($submit_ids as $submit_id) {
                fn_delete_submitted_form($submit_id);
            }
        }
    }
    if ($mode == 'delete') {
        $submit_id = $_REQUEST['submit_id'];
        if (!empty($submit_id)) {
            fn_delete_submitted_form($submit_id);
        }

        return array(CONTROLLER_STATUS_REDIRECT, "sent_forms.manage");
    }

    return array(CONTROLLER_STATUS_OK, "sent_forms.manage");
}

if ($mode == 'manage') {
    $params = $_REQUEST;

    list($forms, $params) = fn_get_submitted_forms($params);
    
    Registry::get('view')->assign('forms', $forms);
    Registry::get('view')->assign('search', $params);
} elseif ($mode == 'update') {
    $params = $_REQUEST;
    
    list($form, $params) = fn_get_submitted_forms($params);

    Registry::get('view')->assign('form_data', fn_get_page_data($form['form_id']));
    Registry::get('view')->assign('form', $form);
}