<?php

use Tygh\Enum\NotificationSeverity;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'check_connection') {
        if ($settings = $_REQUEST['mailbox_data']) {
            $mail_reader = Tygh::$app['addons.helpdesk.mail_reader'];
            if (empty(trim($settings['password'])) && !empty($_REQUEST['mailbox_id'])) $settings['password'] = db_get_field('SELECT password FROM ?:helpdesk_mailboxes WHERE mailbox_id = ?i', $_REQUEST['mailbox_id']) ;
            $mail_reader->setSettings(['host' => "{" . $settings['host'] . "}", 'login' => $settings['email'], 'password' => $settings['password'] ] );
            if ($errors = $mail_reader->getErrors()) {
                fn_set_notification(NotificationSeverity::ERROR, __('error'), __('rus_online_cash_register.connection_refused', ['[error]' => '<br>'. implode('<br>', $errors)]));
            } else {
                fn_set_notification(NotificationSeverity::NOTICE, __('notice'), __('rus_online_cash_register.connection_successful'));
            }
        }
    }
    if ($mode == 'update') {
        if (!empty($_REQUEST['mailbox_data'])) {
            $mailbox_id = fn_update_mailbox($_REQUEST['mailbox_data'], $_REQUEST['mailbox_id']);
        }
        $suffix = ".manage";
    }

    if ($mode == 'delete') {
        if (!empty($_REQUEST['mailbox_id'])) {
            $mailbox_id = fn_delete_mailbox($_REQUEST['mailbox_id']);
        }
        $suffix = ".manage";
    }

    return array(CONTROLLER_STATUS_OK, "mailboxes$suffix");
}

if ($mode == 'manage') {
    $params = $_REQUEST;
    $mailboxes = fn_get_mailboxes($params);

    Tygh::$app['view']->assign('mailboxes', $mailboxes);
} elseif ($mode == 'update') {
    $params = $_REQUEST;
    $mailbox = fn_get_mailboxes($params);

    Tygh::$app['view']->assign('mailbox', array_shift($mailbox));
} elseif ($mode == 'add') {

} elseif ($mode == 'delete') {
    fn_delete_mailbox($_REQUEST['mailbox_id'], true);
}
