<?php

use Tygh\Registry;
use Tygh\Bootstrap;
use Tygh\Storage;
use Tygh\Enum\YesNo;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_trusted_vars (
    'ticket_data'
);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $suffix = '.list';
    if ($mode == 'add') {
        $ticket_data = $_REQUEST['ticket_data'];
        if (!empty($auth['user_id'])) {
            $user_id = $auth['user_id'];
        } elseif (!($user_id = db_get_field('SELECT user_id FROM ?:users WHERE email = ?s', $ticket_data['email']))) {
            if (YesNo::toBool(Registry::get('addons.helpdesk.create_users_for_anonymous'))) {
                list($user_id) = fn_update_user(0, ['email' => $ticket_data['email'], 'name' => $ticket_data['email']], $auth, 'N', true);
            } else {
                // TODO prevent it in GET!!
                fn_set_notification('W', __('warning'), __('helpdesk_user_not_found'));
                fn_save_post_data('ticket_data');
                return array(CONTROLLER_STATUS_REDIRECT, 'tickets.new');
            }
        }

        $ticket_data['users'] = [$user_id];

        $ticket_data['ticket_id'] = fn_update_ticket($ticket_data);
        if (empty($auth['user_id'])) {
            $auth['ticket_ids'][] = $ticket_data['ticket_id'];
        }

        if (!empty($ticket_data['ticket_id'])) {
            $message_id = fn_update_message($ticket_data);
            if ($message_id) {
                fn_set_notification('N', __('notice'), __('message_sent_successfully'));
            }
            $suffix = ".view?ticket_id=".$ticket_data['ticket_id'];
        } else {
            return array(CONTROLLER_STATUS_DENIED);
        }
    }

    if ($mode == 'add_message') {
        $ticket_data = $_REQUEST['ticket_data'];

        if (!empty($ticket_data['ticket_id'])) {
            $message_id = fn_update_message($ticket_data);
            if ($message_id) {
                fn_set_notification('N', __('notice'), __('message_sent_successfully'));
            }
            $ticket_id = $ticket_data['ticket_id'];
            $suffix = ".view?ticket_id=$ticket_id";
        } else {
            return array(CONTROLLER_STATUS_DENIED);
        }
    }

    return array(CONTROLLER_STATUS_OK, "tickets$suffix");
}

if ($mode == 'list') {
    if (!YesNo::toBool(Registry::get('addons.helpdesk.ticketing_system'))) {
        return array(CONTROLLER_STATUS_REDIRECT, "tickets.view");
    }
    $params = $_REQUEST;
    unset($params['user_id']);
    list($tickets, $search) = fn_get_tickets($params);

    Tygh::$app['view']->assign('search', $search);
    Tygh::$app['view']->assign('tickets', $tickets);
} elseif ($mode == 'view') {
    $params = $_REQUEST;

    // if (!empty($params['ticket_id']) && !fn_helpdesk_can_user_access_ticket($_REQUEST['ticket_id'], $auth)) {
    //     return;
    // }

    $params['get_messages'] = true;
    $params['hide_blockquote'] = true;
    $params['sort_order'] = 'asc';
    list($ticket, $params) = fn_get_ticket($params, 0);

    if (empty($ticket)) {
        return array(CONTROLLER_STATUS_DENIED);
    }
    $ticket_data = fn_restore_post_data('ticket_data');
    if (!empty($ticket_data)) {
        Tygh::$app['view']->assign('ticket_data', $ticket_data);
    }

    Tygh::$app['view']->assign('search', $params);
    Tygh::$app['view']->assign('ticket', $ticket);
} elseif ($mode == 'get_file') {
    $params = $_REQUEST;
    if (fn_helpdesk_can_user_access_ticket(db_get_field('SELECT m.ticket_id FROM ?:helpdesk_messages AS m LEFT JOIN ?:helpdesk_message_files AS mf ON mf.message_id = m.message_id WHERE mf.file_id = ?i', $params['file_id']), $auth)) {
        fn_get_helpdesk_file($params);
        exit;
    } else {
        return array(CONTROLLER_STATUS_DENIED);
    }
} elseif ($mode == 'new') {
    Tygh::$app['view']->assign('mailboxes', fn_get_mailboxes());
    $ticket_data = fn_restore_post_data('ticket_data');
    if (!empty($ticket_data)) {
        Tygh::$app['view']->assign('ticket_data', $ticket_data);
    }
}
