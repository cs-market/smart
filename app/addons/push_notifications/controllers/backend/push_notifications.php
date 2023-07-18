<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'send') {
        fn_send_push_notification($_REQUEST['notification_id']);
        $suffix = '.manage';
    }
    if ($mode == 'update') {
        $notification = $_REQUEST['notification_data'];
        $notification_id = fn_update_push_notification($notification, $_REQUEST['notification_id']);
        if ($action == 'send') {
            fn_send_push_notification($notification_id);
        }
        if (!empty($notification_id)) {
            $suffix = ".update?notification_id=$notification_id";
        } else {
            $suffix = '.manage';
        }
    }
    if ($mode == 'm_delete') {
        if (!empty($_REQUEST['notification_ids'])) {
            fn_delete_push_notification($_REQUEST['notification_ids']);
        }

        $suffix = ".manage";
    }
    if ($mode == 'delete') {
        if (!empty($_REQUEST['notification_id'])) {
            fn_delete_push_notification($_REQUEST['notification_id']);
        }

        fn_set_notification('N', __('notice'), __('deleted'));

        $suffix = ".manage";
    }
    return array(CONTROLLER_STATUS_OK, 'push_notifications' . $suffix);
}

if ($mode == 'manage') {
    $params = $_REQUEST;
    list($notifications, $search) = fn_get_push_notifications($params);
    
    Tygh::$app['view']->assign('notifications', $notifications);
    Tygh::$app['view']->assign('search', $search);
} elseif ($mode == 'update') {
    $params = $_REQUEST;
    list($notifications, $search) = fn_get_push_notifications($params);
    if (empty($notifications)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }
    Tygh::$app['view']->assign('notification', array_shift($notifications));
    Tygh::$app['view']->assign('search', $search);
} elseif ($mode == 'add') {
    $params = $_REQUEST;
    if ($params['user_ids']) {
        $notification = array(
            'user_ids' => $params['user_ids'],
        );
    }
    Tygh::$app['view']->assign('notification', $notification);
    Tygh::$app['view']->assign('search', $search);
}
