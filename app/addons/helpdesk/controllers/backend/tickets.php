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
    if ($mode == 'add') {
        $ticket_ids = [];
        $ticket_data = $_REQUEST['ticket_data'];
        $users = explode(',',$ticket_data['users']);
        $ticket_data['users'] = [];
        if (YesNo::toBool($_REQUEST['divide_ticket'])) {
            foreach ($users as $ticket_data['users']) {
                $ticket_ids[] = fn_update_ticket($ticket_data);
            }
        } else {
            $ticket_data['users'] = $users;
            $ticket_ids[] = fn_update_ticket($ticket_data);
        }

        foreach ($ticket_ids as $ticket_data['ticket_id']) {
            fn_update_message($ticket_data);
        }
        if (count($ticket_id) == 1) {
            $suffix = ".update?ticket_id=$ticket_id";
        } else {
            $suffix = ".manage?ticket_id=" . implode(',', $ticket_ids);
        }
    }

    if ($mode == 'update') {
        $ticket_data = $_REQUEST['ticket_data'];

        if (!empty($ticket_data['users'])) {
            $ticket_data['users'] = explode(',',$ticket_data['users']);
        }

        $ticket_id = fn_update_ticket($ticket_data, $_REQUEST['ticket_id']);

        $suffix = ".update?ticket_id=$ticket_id";
    }

    if ($mode == 'add_message') {
        $ticket_data = $_REQUEST['ticket_data'];

        if (Registry::get('addons.helpdesk.service_words')) {
            $service_words = explode(',', Registry::get('addons.helpdesk.service_words'));

            $message = strip_tags ($ticket_data['message']);
            foreach ($service_words as $word) {
                $patterns[] = '(' . $word . ')';
            }
            $master_pattern = '/'.implode("|",$patterns).'/i';
            if (preg_match($master_pattern, $message) ) {
                fn_set_notification('E', __('error'), __('incorrect_filling_message'));
                fn_save_post_data('ticket_data');
                return array(CONTROLLER_STATUS_OK);
            }
        }
        $uploaded_data = fn_filter_uploaded_data('ticket_data');

        foreach ($uploaded_data as $key => $file) {
            $ticket_data['attachment'][$file['name']] = file_get_contents($file['path']);
        }

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
    if ($mode == 'update_message'){
        $message_data = $_REQUEST['ticket_data'];
        $message_id = fn_update_message($message_data, $_REQUEST['message_id']);
        $suffix = ".update_message?message_id=" . $message_id;
    }

    return array(CONTROLLER_STATUS_OK, "tickets$suffix");
}

if ($mode == 'manage') {
    $params = $_REQUEST;

    list($tickets, $search) = fn_get_tickets($params);

    Tygh::$app['view']->assign('search', $search);
    Tygh::$app['view']->assign('tickets', $tickets);
} elseif ($mode == 'update') {
    $params = $_REQUEST;
    if (!empty($params['ticket_id'])) {
        list($ticket, $params) = fn_get_ticket($params);

        if (empty($ticket)) {
            return array(CONTROLLER_STATUS_DENIED);
        }
        Tygh::$app['view']->assign('ticket', $ticket);
        Tygh::$app['view']->assign('mailboxes', fn_get_mailboxes());
    }
} elseif ($mode == 'add') {
    Tygh::$app['view']->assign('mailboxes', fn_get_mailboxes());
} elseif ($mode == 'view') {
    $params = $_REQUEST;
    $params['get_messages'] = true;
    $params['hide_blockquote'] = true;
    list($ticket, $params) = fn_get_ticket($params);

    if (empty($ticket)) {
        return array(CONTROLLER_STATUS_DENIED);
    }
    $ticket_data = fn_restore_post_data('ticket_data');
    if (!empty($ticket_data)) {
        Tygh::$app['view']->assign('ticket_data', $ticket_data);
    }

    Tygh::$app['view']->assign('search', $params);
    Tygh::$app['view']->assign('ticket', $ticket);

    Tygh::$app['view']->assign('templates', fn_get_message_templates(array('ticket_id' => $ticket['ticket_id'])));
} elseif ($mode == 'update_message') {
    $old_message = db_get_row("SELECT * FROM ?:helpdesk_messages WHERE message_id = ?i", $_REQUEST['message_id']);
    $params['ticket_id'] = $old_message['ticket_id'];
    $params = $_REQUEST;
    list($message) = fn_get_messages($params);
    Tygh::$app['view']->assign('message', array_shift($message));
} elseif ($mode == 'move_message') {
        $params = $_REQUEST;

        list($messages, ) = fn_get_messages(array('message_id' => $params['message_id']));
        $message = array_shift($messages);

        if (isset($message['files'])) foreach ($message['files'] as $file) {
            list($filesize, $filename) = Storage::instance('helpdesk_files')->put($params['ticket_id'] . '/' . $message['message_id'] . '/' . $file['filename'], array(
                'file' => Storage::instance('helpdesk_files')->getAbsolutePath($message['ticket_id'] . '/' . $params['message_id'] . '/' . $file['filename'])
            ));
        }

        db_query('UPDATE ?:helpdesk_messages SET `ticket_id` = ?i WHERE message_id = ?i', $params['ticket_id'], $params['message_id']);
    return array(CONTROLLER_STATUS_REDIRECT, "tickets.view&amp;ticket_id=".$params['ticket_id']);
} elseif ($mode == 'delete') {
    if (isset($_REQUEST['spam'])) {
        $params['ticket_id'] = $_REQUEST['ticket_id'];
        $params['user_type'] = array('C');
        $spam_users = fn_get_ticket_users($params);
        foreach ($spam_users as $user) {
            db_query("UPDATE ?:users SET `status` = ?s WHERE user_id = ?i", 'D', $user['user_id']);
        }
    }

    $result = fn_delete_ticket($_REQUEST['ticket_id']);
    return array(CONTROLLER_STATUS_OK);
} elseif ($mode == 'close') {
    if (!empty($_REQUEST['ticket_id'])) {
        $ticket_id = $_REQUEST['ticket_id'];
        $data['status'] = 'C';
        db_query("UPDATE ?:helpdesk_messages SET status = 'C' WHERE ticket_id = ?i", $ticket_id);
    }
    return array(CONTROLLER_STATUS_REDIRECT, "tickets.manage");
} elseif ($mode == 'search') {
    if(!empty($_REQUEST['q'])) {
        $params = $_REQUEST;
        list($messages, $params) = fn_get_messages($params);
        Tygh::$app['view']->assign('search', $params);
        Tygh::$app['view']->assign('ticket', array('messages' => $messages));
    }
} elseif ($mode == 'delete_message') {
    $message_id = $_REQUEST['message_id'];
    fn_delete_helpdesk_message($message_id);
    return array(CONTROLLER_STATUS_OK);
} elseif ($mode == 'get_file') {
    $params = $_REQUEST;
    fn_get_helpdesk_file($params);
    exit;
} elseif ($mode == 'get_mail') {
    fn_helpdesk_get_mail();
    exit;
} elseif ($mode == 'send_mail') {
    fn_helpdesk_send_mail();
    exit;
} elseif ($mode == 'cron_job') {
    fn_helpdesk_get_mail();
    fn_helpdesk_send_mail();
    exit;
}
