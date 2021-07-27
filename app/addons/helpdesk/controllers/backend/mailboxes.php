<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
