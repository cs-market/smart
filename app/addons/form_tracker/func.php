<?php
/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*      Copyright (c) 2013 CS-Market Ltd. All rights reserved.             *
*                                                                         *
*  This is commercial software, only users who have purchased a valid     *
*  license and accept to the terms of the License Agreement can install   *
*  and use this program.                                                  *
*                                                                         *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*  PLEASE READ THE FULL TEXT OF THE SOFTWARE LICENSE AGREEMENT IN THE     *
*  "license agreement.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.  *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * **/

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_form_tracker_addon_install() {
    $statuses = array(
        'N' => array(
            'status' => 'N',
            'is_default' => 'Y',
            'description' => __('new'),
        ),
        'P' => array(
            'status' => 'P',
            'is_default' => 'N',
            'description' => __('processed'),
        ),
        'C' => array(
            'status' => 'C',
            'is_default' => 'N',
            'description' => __('completed'),
        ),
        'D' => array(
            'status' => 'D',
            'is_default' => 'N',
            'description' => __('declined'),
        )
    );

    foreach ($statuses as $status_data) {
        fn_update_status('', $status_data, STATUSES_FORM_TRACKER);
    }
}

function fn_form_tracker_addon_uninstall() {
    $statuses = fn_get_statuses_by_type(STATUSES_FORM_TRACKER);
    foreach ($statuses as $status_id => $status) {
        fn_delete_status_by_id($status_id);
    }
}

function fn_form_tracker_update_page_post(&$page_data, &$page_id, &$lang_code) {
    if (!empty($page_data['form'])) {
        $general_data = empty($page_data['form']['general']) ? array() : $page_data['form']['general'];
        if (!empty($general_data)) {
            foreach ($general_data as $type => $data) {
                if ($type != FORM_IS_TRACKED) continue;
                $elm_id = db_get_field("SELECT element_id FROM ?:form_options WHERE page_id = ?i AND element_type = ?s", $page_id, $type);
                $_data = array (
                    'element_type' => $type,
                    'page_id' => $page_id,
                    'status' => 'A',
                    'value' => $data,
                );
                if (empty($elm_id)) {
                    $elm_id = db_query('INSERT INTO ?:form_options ?e', $_data);
                } else {
                    db_query('UPDATE ?:form_options SET ?u WHERE element_id = ?i', $_data, $elm_id);
                }
            }
        }
    }
}

function fn_form_tracker_get_page_data(&$page_data)
{
    if (!empty($page_data['page_type']) && $page_data['page_type'] == PAGE_TYPE_FORM) {
        $value = db_get_field('SELECT value FROM ?:form_options WHERE page_id = ?i AND element_type = ?s', $page_data['page_id'], FORM_IS_TRACKED);
        if ($value) {
            $page_data['form']['general'][FORM_IS_TRACKED] = $value;
        }
    }
}

function fn_form_tracker_send_form(&$page_data, $form_values, $result, $from, $sender, $attachments, $is_html, $subject) {
    // if form trackable
    $submitted_form = array(
        'form_data' => $form_values,
        'form_id' => $page_data['page_id'],
        'user_id' => Tygh::$app['session']['auth']['user_id'],
        'comments' => '',
        'timestamp' => TIME,
        'status' => 'N'
    );

    fn_update_submitted_form($submitted_form);

    if (defined('AJAX_REQUEST')) {
        fn_set_notification('N', __('notice'), $page_data['form']['general']['L']);
    }
}

function fn_update_submitted_form($submitted_form, $submit_id = 0) {
    if (!empty($submitted_form)) {
        if (empty($submit_id)) {
            if (isset($submitted_form['form_data']) && !empty($submitted_form['form_data'])) {
                $submitted_form['form_data'] = serialize($submitted_form['form_data']);
            }
            $submit_id = db_query('INSERT INTO ?:sent_forms ?e', $submitted_form);
        } else {
            if (isset($submitted_form['form_data']) && !empty($submitted_form['form_data'])) {
                $submitted_form['form_data'] = serialize($submitted_form['form_data']);
            }
            db_query('UPDATE ?:sent_forms SET ?u WHERE submit_id = ?i', $submitted_form, $submit_id);
        }
    }

    return $submit_id;
}

function fn_delete_submitted_form($submit_id) {
    $deleted = false;

    if (!empty($submit_id)) {
        $affected_rows = db_query("DELETE FROM ?:sent_forms WHERE submit_id = ?i", $submit_id);

        if ($affected_rows != 0) {
            $deleted = true;
        }
    }

    return $deleted;
}

function fn_get_submitted_forms($params) {
    $default_params = array (
        'page' => 1,
        'items_per_page' => '10'
    );
    $params = array_merge($default_params, $params);

    $limit = '';
    $condition = "";
    $order_by = " ORDER BY timestamp desc";

    if (isset($params['submit_id'])) {
        $condition .= db_quote(" AND submit_id = ?i", $params['submit_id']);
    }

    if (isset($params['status'])) {
        $condition .= db_quote(" AND status = ?s", $params['status']);
    }

    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT count(*) FROM ?:sent_forms WHERE 1 $condition");
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    fn_set_hook('get_submitted_forms_pre', $params, $condition, $order_by, $limit);

    $forms = db_get_array("SELECT * FROM ?:sent_forms WHERE 1 $condition $order_by $limit");

    $pages_params = array();
    $pages_params['item_ids'] = implode(',', fn_array_column($forms, 'form_id')); 
    $pages_params['get_tree'] = 'plain';
    list($pages, ) = fn_get_pages($pages_params);
    $page_names = fn_array_column($pages, 'page', 'page_id');
    
    foreach ($forms as $key => &$form) {
        $form['form_data'] = unserialize($form['form_data']);
        $form['form_name'] = $page_names[$form['form_id']];
    }

    if (isset($params['submit_id'])) {
        $forms = array_shift($forms);
    }
    return array($forms, $params);
}