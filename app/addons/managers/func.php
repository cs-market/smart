<?php

use Tygh\Registry;
use Tygh\Enum\UserTypes;
use Tygh\Enum\SiteArea;
use Tygh\Enum\UserRoles;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_get_managers($params = []) {
    if (!is_array($params)) $params = [];
    $condition = '';
    $params['user_role'] = isset($params['user_role']) ? $params['user_role'] : [UserRoles::manager(), UserRoles::supervisor(), UserRoles::operator()];

    list($managers, ) = fn_get_users($params, Tygh::$app['session']['auth']);

    $managers = array_map(function($m) {
        $m['firstname'] = !empty($m['firstname']) ? $m['firstname'] : $m['email'];
        return $m;
    }, $managers);
    $roles = UserRoles::getList();

    array_walk($managers, function(&$v) use ($roles) {
        if (!empty($v['user_role'])) $v['user_role_descr'] = __($roles[$v['user_role']]);
    });

    if (isset($params['group_by'])) {
        if (fn_allowed_for('MULTIVENDOR')) {
            $params['group_by'] = 'company_name';
        }
        $managers = fn_array_group($managers, $params['group_by']);
    }

    return $managers;
}

function fn_managers_get_user_info($user_id, $get_profile, $profile_id, &$user_data) {
    // get managers for single user
    if (SiteArea::isAdmin(AREA) && !empty($user_id)) {
        if ($user_data['user_type'] == UserTypes::CUSTOMER) {
            $user_data['managers'] = fn_get_managers(['user_managers' => $user_id]);
        } elseif (fn_user_roles_is_management_user($user_id)) {
            list($user_data['users']) = fn_get_users(['manager_users' => $user_id], Tygh::$app['session']['auth']);
        }
    }
}

function fn_managers_user_roles_get_list(&$roles, $type) {
    if (empty($type) || $type != 'C') {
        $roles['M'] = 'manager';
        $roles['S'] = 'supervisor';
        $roles['O'] = 'operator';
    }
}

function fn_get_user_role($user_id = 0) {
    $role = '';
    if (empty($user_id) && !empty(Tygh::$app['session']['auth']['user_role'])) {
        $role = Tygh::$app['session']['auth']['user_role'];
    } else {
        if (empty($user_id)) $user_id = Tygh::$app['session']['auth']['user_id'];
        $role = db_get_field('SELECT user_role FROM ?:users WHERE user_id = ?i', $user_id);
    }
    return $role;
}

function fn_user_roles_is_manager($user_id = 0) {
    return (fn_get_user_role($user_id) == UserRoles::manager());
}

function fn_user_roles_is_supervisor($user_id = 0) {
    return (fn_get_user_role($user_id) == UserRoles::supervisor());
}

function fn_user_roles_is_operator($user_id = 0) {
    return (fn_get_user_role($user_id) == UserRoles::operator());
}

function fn_user_roles_is_management_user($user_id = 0) {
    if (empty($user_id) && !empty(Tygh::$app['session']['auth']['user_role'])) {
        $role = Tygh::$app['session']['auth']['user_role'];
    } else {
        if (empty($user_id)) $user_id = Tygh::$app['session']['auth']['user_id'];
        $role = db_get_field('SELECT user_role FROM ?:users WHERE user_id = ?i', $user_id);
    }

    return (in_array($role, [UserRoles::manager(), UserRoles::supervisor(), UserRoles::operator()]) );
}

function fn_managers_get_orders($params, $fields, $sortings, &$condition, &$join, &$group) {
    if (UserRoles::is_management_user()) {
        $params['manager_users'] = Tygh::$app['session']['auth']['user_id'];
    }
    if (!empty($params['manager_users'])) {
        list($users, ) = fn_get_users(['manager_users' => $params['manager_users']], Tygh::$app['session']['auth']);

        if ($users) {
            $condition .= db_quote(' AND ?:orders.user_id IN (?a)', array_column($users, 'user_id'));
        } else {
            $condition .= db_quote(' AND 0');
        }
    }
}

function fn_managers_get_order_info(&$order, $additional_data) {
    if (!empty($order) && SiteArea::isAdmin(AREA) && Registry::get('runtime.mode') == 'details' && UserRoles::is_management_user()) {
        list($users, ) = fn_get_users([], Tygh::$app['session']['auth']);
        if (!in_array($order['user_id'], array_column($users, 'user_id'))) {
            $order = false;
        }
    }
}

function fn_managers_get_users(&$params, &$fields, &$sortings, &$condition, &$join, $auth) {
    if (UserRoles::is_management_user()) {
        if ($params['user_type'] == 'C') $params['manager_users'] = $auth['user_id'];
    }

    if (!empty($params['manager_users'])) {
        if (!is_array($params['manager_users'])) {
            $params['manager_users'] = explode(',', $params['manager_users']);
        }
        $join .= db_quote(' LEFT JOIN ?:user_managers ON ?:user_managers.user_id = ?:users.user_id');
        $condition['get_managers'] = db_quote(' AND ?:user_managers.manager_id in (?a) ', $params['manager_users']);
    } elseif (!empty($params['user_managers'])) {
        $join .= db_quote(' LEFT JOIN ?:user_managers ON ?:user_managers.manager_id = ?:users.user_id');
        $condition['get_manager_users'] = db_quote(' AND ?:user_managers.user_id = ?i ', $params['user_managers']);
    }
}

function fn_update_user_managers($user_id, $users, $update_type = 'for_user') {
    if ($update_type == 'for_user') {
        $base_field = 'user_id';
        $update_field = 'manager_id';
    } else {
        $base_field = 'manager_id';
        $update_field = 'user_id';
    }

    db_query("DELETE FROM ?:user_managers WHERE ?f = ?i", $base_field, $user_id);

    if (!empty($users)) {
        $udata = [];
        if (!is_array($users)) {
            $users = explode(',', $users);
        } else {
            $users = array_column($users, 'user_id');
        }
        // API and ExIm OPERATES BY EMAILS!
        if (defined('API') || Registry::get('runtime.controller') == 'exim') {
            $users = db_get_fields(
            "SELECT user_id FROM ?:users WHERE email IN (?a)",
            array_map('trim', $users)
            );
        }

        foreach ($users as $id) {
            if (!empty($id)) $udata[] = [$base_field => $user_id, $update_field => $id];
        }

        if (!empty($udata)) db_query('INSERT INTO ?:user_managers ?m', $udata);
    }
}

function fn_managers_update_user_profile_pre($user_id, &$user_data, $action = '') {
    if (SiteArea::isAdmin(AREA) && !empty($user_data['user_id'])) {
        if (isset($user_data['managers']) && $user_data['user_type'] == 'C') fn_update_user_managers($user_id, $user_data['managers'], 'for_user');
        if (isset($user_data['manager_users']) && $user_data['user_type'] != 'C') fn_update_user_managers($user_id, $user_data['manager_users'], 'for_manager');
    }
}

function fn_managers_vendor_communication_add_thread_message_post( $thread_full_data, $result) {
    if ($thread_full_data['last_message_user_type'] != 'A') {
        $managers = fn_get_managers(['user_managers' => $thread_full_data['last_message_user_id']]);
        if (!empty($managers)) {
            $vendor_email = array_column($managers, 'email');
            if (!empty($thread_full_data['last_message_user_id'])) {
                $message_from = fn_vendor_communication_get_user_name($thread_full_data['last_message_user_id']);
            }

            $email_data = array(
                'area' => 'A',
                'email' => $vendor_email,
                'email_data' => array(
                    'thread_url' => fn_url("vendor_communication.view&thread_id={$thread_data['thread_id']}", 'V'),
                    'message_from' => !empty($message_from) ? $message_from : fn_get_company_name($thread_data['company_id']),
                ),
                'template_code' => 'vendor_communication.notify_admin',
            );

            $result = fn_vendor_communication_send_email_notification($email_data);
        }
    }
}

function fn_managers_place_order($order_id, $action, $order_status, $cart, $auth) {
    $order_info = fn_get_order_info($order_id);
    $field = (isset($cart['order_id']) && !empty($cart['order_id'])) ? 'notify_manager_order_update' : 'notify_manager_order_create';
    if (db_get_field("SELECT $field FROM ?:companies WHERE company_id = ?i", $order_info['company_id']) == 'Y') {
        $mailer = Tygh::$app['mailer'];
        list($shipments) = fn_get_shipments_info(array('order_id' => $order_info['order_id'], 'advanced_info' => true));
        $use_shipments = !fn_one_full_shipped($shipments);
        $payment_id = !empty($order_info['payment_method']['payment_id']) ? $order_info['payment_method']['payment_id'] : 0;
        $company_lang_code = fn_get_company_language($order_info['company_id']);
        $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true, false, ($order_info['lang_code'] ? $order_info['lang_code'] : CART_LANGUAGE), $order_info['company_id']);
        $status_settings = $order_statuses[$order_info['status']]['params'];

        $managers = fn_get_managers(['user_managers' => $order_info['user_id'], 'user_role' => UserRoles::manager()]);
        $email_template_name = 'order_notification.' . (($action == 'save') ? 'y' : 'o');

        $order_status = ($order_status == 'N') ? 'O' : $order_status;
        $mailer->send(array(
            'to' => array_column($managers, 'email'),
            'from' => 'default_company_orders_department',
            'reply_to' => $order_info['email'],
            'data' => array(
                'order_info' => $order_info,
                'shipments' => $shipments,
                'use_shipments' => $use_shipments,
                'order_status' => fn_get_status_data($order_status, STATUSES_ORDER, $order_info['order_id'], $company_lang_code),
                'payment_method' => fn_get_payment_data($payment_id, $order_info['order_id'], $company_lang_code),
                'status_settings' => $status_settings,
                'profile_fields' => fn_get_profile_fields('I', '', $company_lang_code)
            ),
            'template_code' => $email_template_name,
            'tpl' => 'orders/order_notification.tpl', // this parameter is obsolete and is used for back compatibility
            'company_id' => $order_info['company_id'],
        ), 'A', $company_lang_code);
    }
}

function fn_managers_user_init(&$auth, &$user_info) {
    if (SiteArea::isStorefront(AREA)) {
        $user_info['managers'] = fn_get_managers(['user_managers' => $auth['user_id']]);
    }
}

function fn_managers_generate_sales_report(&$params, &$elements_join, &$elements_condition) {
    if (!empty($params['manager'])) {
        $elements_join['user_managers'] = db_quote(' LEFT JOIN ?:user_managers AS um ON um.user_id = u.user_id ');
        $elements_condition['user_managers'] = db_quote(' AND um.manager_id = ?i', $params['manager']);
    }
}

function fn_managers_generate_sales_report_post($params, &$row, $user) {
    if ($params['show_manager'] == 'Y') {
        $roles = UserRoles::getList(UserTypes::ADMIN);
        unset($roles[UserRoles::CUSTOMER]);
        array_walk($user['managers'], function(&$v) {
            $v['name'] = trim($v['firstname'] . ' ' . $v['lastname']);
        });

        $managers = fn_array_group($user['managers'], 'user_role');
        foreach ($roles as $role => $role_descr) {
            $row[__($role_descr)] = implode(',', array_column($managers[$role], 'name'));
        }
    }
}

function fn_managers_delete_user($user_id, $user_data) {
    db_query('DELETE FROM ?:user_managers WHERE user_id = ?i OR manager_id = ?i', $user_id);
}

function fn_managers_get_tickets_params(&$params, $condition, $join) {
    if (ACCOUNT_TYPE == 'vendor' && !UserRoles::is_management_user()) {
        unset($params['user_id']);
    }
}

function fn_managers_send_form(&$page_data, $form_values, $result, $from, $sender, $attachments, $is_html, $subject) {
    if (Tygh::$app['session']['auth']['user_id']) {
        $managers = fn_get_managers(['user_managers' => Tygh::$app['session']['auth']['user_id'], 'user_role' => UserRoles::manager()]);
        if (!empty($managers)) {
            $page_data['form']['general'][FORM_RECIPIENT] = [$page_data['form']['general'][FORM_RECIPIENT]] + array_column($managers, 'email', 'name');
        }
    }
}

function fn_managers_update_ticket_pre(&$data) {
    $sender = reset($data['users']);
    $managers = fn_get_managers(['user_managers' => $sender]);
    $data['users'] = array_unique(array_merge($data['users'], array_column($managers, 'user_id')));
}

function fn_managers_sales_reports_dynamic_conditions($type, $condition, $users) {
    if ($type == 'managers') {
        $type = 'user';
        list($users, ) = fn_get_users(['manager_users' => $condition], Tygh::$app['session']['auth']);
        $users = array_column($users, 'user_id');
        $condition = array_combine($users, $users);
    }
}
