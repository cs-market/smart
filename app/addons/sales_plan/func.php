<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

require_once(Registry::get('config.dir.functions') . 'fn.sales_reports.php');

if (!is_callable('fn_ts_this_day')) {
function fn_ts_this_day($timestamp){
    $calendar_format = "d/m/Y";
    if (Registry::get('settings.Appearance.calendar_date_format') == 'month_first') {
        $calendar_format = "m/d/Y";
    }
    $ts = fn_parse_date(date($calendar_format, $timestamp));
    return $ts;
}
}

function fn_ts_this_week($timestamp){
    $ts = mktime(0, 0, 0, 1, (date("W", $timestamp) - 1 )  * 7, date("Y", $timestamp));
    return $ts;
}


function fn_ts_this_month($timestamp){
    $calendar_format = "01/m/Y";
    if (Registry::get('settings.Appearance.calendar_date_format') == 'month_first') {
        $calendar_format = "m/01/Y";
    }
    $ts = fn_parse_date(date($calendar_format, $timestamp));
    return $ts;
}

function fn_sales_plan_post_delete_user($user_id, $user_data, $result) {
    if ($result) {
        db_query("DELETE FROM ?:sales_plan WHERE user_id = ?i", $user_id);
    }
}

// что за фигня?
function fn_sales_plan_get_users($params, $fields, $sortings, &$condition, &$join, $auth) {
    if (isset($params['usergroup_ids'])) {
        if (!empty($params['usergroup_ids'])) {
            $join .= db_quote(" LEFT JOIN ?:usergroup_links ON ?:usergroup_links.user_id = ?:users.user_id AND ?:usergroup_links.usergroup_id in (?a)", $params['usergroup_ids']);
            $condition['usergroup_links'] = " AND ?:usergroup_links.status = 'A'";
        } else {
            $join .= " LEFT JOIN ?:usergroup_links ON ?:usergroup_links.user_id = ?:users.user_id AND ?:usergroup_links.status = 'A'";
            $condition['usergroup_links'] = " AND ?:usergroup_links.user_id IS NULL";
        }
    }
}

function fn_sales_plan_delete_company($company_id, $result) {
    if ($result) {
        db_query("DELETE FROM ?:sales_plan WHERE company_id = ?i", $company_id);
    }
}

// TODO CHECK THIS CODE
function fn_sales_plan_create_order($order) {
    if (Registry::get('addons.managers.status') == 'A') {
        $manager = db_get_field("SELECT u.email FROM ?:users AS u LEFT JOIN ?:user_managers AS um ON um.manager_id = u.user_id WHERE um.user_id = ?i AND um.manager_id IN (SELECT user_id FROM ?:users LEFT JOIN ?:companies ON ?:users.company_id = ?:companies.company_id WHERE user_type = ?s AND ?:users.company_id = ?i AND ?:companies.notify_manager_order_insufficient = 'Y')", $order['user_id'], 'V', $order['company_id']);

        if (!empty($manager)) {
            $notification_data[$manager]['less_placed'][] = $order['user_id'];
        }
        
        $mailer = Tygh::$app['mailer'];

        if (!empty($notification_data)) {
            foreach ($notification_data as $manager_email => $data) {
                $mailer->send(array(
                    'to' => $manager_email,
                    'from' => 'default_company_orders_department',
                    'data' => array('data' => $data),
                    'tpl' => 'addons/sales_plan/sales_notification.tpl',
                ), 'A');
            }
        }
    }
}

// может быть объединить с хуком выше?
function fn_sales_plan_place_order($order_id, $action, &$order_status, $cart, $auth) {
    $user_data = fn_get_user_info($cart['user_id']);
    if ($user_data['approve_order_action'] != 'D') {
        if ($user_data['approve_order_action'] == 'P') {
            $order = fn_get_order_info($order_id);
            if (isset($user_data['plan'][$order['company_id']]['amount_plan']) && !empty($user_data['plan'][$order['company_id']]['amount_plan']) && $order['total'] > $user_data['plan'][$order['company_id']]['amount_plan'] && $order_status == STATUS_INCOMPLETED_ORDER) {
                $order_status = 'P';
            }
        } elseif ($order_status == STATUS_INCOMPLETED_ORDER) {
            $order_status = 'P';
        }
    }
}

function fn_sales_plan_get_user_info($user_id, $get_profile, $profile_id, &$user_data) {
    if (fn_allowed_for('MULTIVENDOR')) {
        $condition = '';
        if (Registry::get('runtime.company_id')) {
            $condition .= db_quote(' AND company_id = ?i', Registry::get('runtime.company_id'));
        }
        $user_data['plan'] = db_get_hash_array("SELECT * from ?:sales_plan WHERE user_id = ?i $condition", 'company_id', $user_id);
    }
}
