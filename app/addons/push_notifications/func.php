<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

function fn_get_push_notifications($params, $items_per_page = 0) {
    $default_params = array(
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);
  
    $condition = $limit = '';

    if (Registry::get('runtime.company_id')) {
        $condition .= db_quote(" AND company_id = ?i", Registry::get('runtime.company_id'));
    }

    if (!empty($params['notification_id'])) {
        $condition .= db_quote(" AND notification_id = ?i", $params['notification_id']);
    }

    if (!empty($params['limit'])) {
        $limit = db_quote(' LIMIT ?i', $params['limit']);
    }
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT count(*) FROM ?:push_notifications WHERE 1 $condition");
        $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
    }

    $push_notifications = db_get_array("SELECT * FROM ?:push_notifications WHERE 1 $condition ORDER BY notification_id ASC $limit");

    return array($push_notifications, $params);
}

function fn_update_push_notification($notification, $notification_id = 0) {
    if ($notification_id) {
        list($db_notification, ) = fn_get_push_notifications(array('notification_id' => $notification_id));
        if (!empty($db_notification)) {
            db_query('UPDATE ?:push_notifications SET ?u WHERE notification_id = ?i', $notification, $notification_id);
        } else {
            $notification_id = 0;
        }
    } else {
        if (Registry::get('runtime.company_id')) {
            $notification['company_id'] = Registry::get('runtime.company_id');
        }
        $notification_id = db_query('INSERT INTO ?:push_notifications ?e', $notification);
    }
    return $notification_id;
}

function fn_delete_push_notification($notification_ids) {
    if (!is_array($notification_ids)) {
        $notification_ids = array($notification_ids);
    }
    foreach ($notification_ids as $notification_id) {
        list($db_notification, ) = fn_get_push_notifications(array('notification_id' => $notification_id));
        if (!empty($db_notification)) {
            db_query('DELETE FROM ?:push_notifications WHERE notification_id = ?i', $notification_id);
        }
    }
}

function fn_send_push_notification($notification_id) {
    list($notifications, ) = fn_get_push_notifications(array('notification_id' => $notification_id));
    $notification = array_shift($notifications);
    if (!empty($notification) && !empty($notification['user_ids'])) {
        foreach (explode(',', $notification['user_ids']) as $user_id) {
            fn_mobile_app_notify_user($user_id, $notification['title'], strip_tags(html_entity_decode($notification['body'])), '', 1);
        }
        db_query("UPDATE ?:push_notifications SET ?u WHERE notification_id = ?i", array('sent_date' => time()), $notification_id);
        fn_set_notification('N', __('notice'), __('sent'));
    }
}

function fn_push_notifications_get_users_pre(&$params, $auth, $items_per_page, $custom_view) {
    if (isset($params['mobile_app']) && $params['mobile_app'] == 'Y') {
        unset($params['exclude_user_types']);
    }
}

function fn_push_notifications_get_users($params, $fields, $sortings, &$condition, &$join, $auth) {
    if (isset($params['mobile_app']) && $params['mobile_app'] == 'Y') {
        $join .= db_quote(" RIGHT JOIN ?:mobile_app_notification_subscriptions AS mans ON mans.user_id = ?:users.user_id");
        if (!empty($params['app_name'])) {
            $condition['app_name'] = db_quote(' AND mans.app_name = ?s', $params['app_name']);
        }
        if (!empty($params['app_version'])) {
            $condition['app_version'] = db_quote(' AND mans.app_version = ?s', $params['app_version']);
        }
    }
}

function fn_push_notifications_app_update_notification_subscription($user_id, $params) {
    $params['user_id'] = $user_id;

    db_replace_into('mobile_app_notification_subscriptions', $params);

    $subscription = fn_mobile_app_get_notification_subscriptions(array(
        'user_id'   => $user_id,
        'device_id' => $params['device_id'],
        'platform'  => $params['platform'],
    ));

    $subscription = reset($subscription);

    return (int)$subscription['subscription_id'];
}

function fn_push_notifications_get_mobile_table_values($param) {
    if ($param)
    return db_get_fields("SELECT DISTINCT(?p) FROM ?:mobile_app_notification_subscriptions WHERE ?p != ''", $param, $param);
}

function fn_push_notifications_helpdesk_send_message_pre(&$message, $mailbox) {
    foreach ($message['users'] as $user_id => $user) {
        $result = fn_mobile_app_notify_user($user_id, $message['subject'], strip_tags(html_entity_decode($message['message'])), '', 1);
        if ($result) unset($message['users'][$user_id]);
    }
}
