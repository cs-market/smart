<?php

use Tygh\Registry;
use Tygh\Storage;
use Tygh\Enum\SiteArea;
use Tygh\Enum\UserTypes;
use Tygh\Enum\YesNo;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_get_mailboxes($params = array()) {
    $condition = '';

    if (isset($params['mailbox_id'])) {
        $condition .= db_quote(" AND mailbox_id = ?i", $params['mailbox_id']);
    }

    if (Registry::get('runtime.company_id')) {
        $params['company_id'] = Registry::get('runtime.company_id');
    }

    if (isset($params['company_id']) && !empty($params['company_id'])) {
        $condition .= fn_get_company_condition('?:helpdesk_mailboxes.company_id', true, $params['company_id'], false, true);   
    }
    if (SiteArea::isStorefront(AREA)) {
        $condition .= " AND status = 'A'";
    }

    fn_set_hook('get_mailboxes_pre', $condition);

    $mailboxes = db_get_hash_array("SELECT * FROM ?:helpdesk_mailboxes WHERE 1 $condition", 'mailbox_id');

    return $mailboxes;
}

function fn_update_mailbox($mailbox_data, $mailbox_id = 0) {
    if ($mailbox_id) {
        if (empty(trim($mailbox_data['password']))) unset($mailbox_data['password']);
        db_query('UPDATE ?:helpdesk_mailboxes SET ?u WHERE mailbox_id = ?i', $mailbox_data, $mailbox_id);
    } else {
        $mailbox_id = $ticket_users['ticket_id'] = db_query("INSERT INTO ?:helpdesk_mailboxes ?e", $mailbox_data);
    }

    return $mailbox_id;
}

function fn_delete_mailbox($mailbox_id = 0, $delete_tickets = true) {
    $res = db_query('DELETE FROM ?:helpdesk_mailboxes WHERE mailbox_id = ?i', $mailbox_id);

    if ($delete_tickets) {
        $tickets = db_get_fields("SELECT ticket_id FROM ?:helpdesk_tickets WHERE mailbox_id = ?i", $mailbox_id);
        foreach ($tickets as $ticket_id) {
            fn_delete_ticket($ticket_id);
        }
    }
    return $res;
}

function fn_get_message_templates($params = array()) {
    $params['user_id'] = !empty($params['user_id']) ? $params['user_id'] : Tygh::$app['session']['auth']['user_id'];

    $condition = db_quote(" (user_id = ?i or is_global = 'Y')", $params['user_id']);

    if (!empty($params['template_id'])) {
        $condition .= db_quote(' AND template_id = ?i', $params['template_id']);
    }

    $templates = db_get_hash_multi_array("SELECT * FROM ?:helpdesk_templates WHERE $condition ORDER BY position", array('is_global','template_id'));

    // START REPLACEMENT
    if (isset($params['ticket_id'])) {
        $ticket_users = fn_get_ticket_users(array('ticket_id' => $params['ticket_id'], 'user_type' => 'C'));

        $user = array_shift($ticket_users);
        $replacement['%CLIENTNAME'] = !empty($user['firstname']) ? $user['firstname'] : $user['lastname'];
    }

    if (isset($replacement) && is_array($replacement)) {
        foreach ($templates as &$template_type) {
            foreach ($template_type as $key => &$template) {
                $template['template'] = strtr($template['template'], $replacement);
            }
        }
    }

    return $templates;
}

function fn_update_template($template, $template_id = 0) {
    if (!empty($template_id)) {
        $current_data = db_get_row('SELECT * FROM ?:helpdesk_templates WHERE template_id = ?i', $template_id);
        if ($current_data['user_id'] == Tygh::$app['session']['auth']['user_id']) {
            unset($template['template_id']);
            $template_id = db_query('UPDATE ?:helpdesk_templates SET ?u WHERE template_id = ?i', $template, $template_id);
        } else {
            fn_set_notification('W', __('warning'), __('helpdesk.template_owned_by') . ' ' . fn_get_user_name($current_data['user_id']));
        }
    } else {

        $template['user_id'] = !empty($data['user_id']) ? $data['user_id'] : Tygh::$app['session']['auth']['user_id'];
        $template_id = $template['template_id'] = db_query("INSERT INTO ?:helpdesk_templates ?e", $template);
    }
    return $template_id;
}

function fn_delete_message_template($template_id = 0) {
    $res = false;
    $current_data = db_get_row('SELECT * FROM ?:helpdesk_templates WHERE template_id = ?i', $template_id);
    if ($current_data['user_id'] == Tygh::$app['session']['auth']['user_id']) {
        $res = db_query("DELETE FROM ?:helpdesk_templates WHERE template_id = ?i", $template_id);
    } else {
        fn_set_notification('W', __('warning'), __('helpdesk.template_owned_by') . ' ' . fn_get_user_name($current_data['user_id']));
    }
    return $res;
}

function fn_get_tickets($params = array(), $items_per_page = 10) {
    $condition = "1";
    $join = [];
    $order = "";
    $group = "GROUP BY t.ticket_id";
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page,
        'user_id' => Tygh::$app['session']['auth']['user_id'],
    );

    $params = fn_array_merge($default_params, $params);
    $user_info = fn_get_user_short_info($params['user_id']);

    if (SiteArea::isAdmin(AREA) && !fn_is_restricted_admin($user_info) && UserTypes::isAdmin($user_info['user_type'])) {
        unset($params['user_id']);
    }

    fn_set_hook('get_tickets_params', $params, $condition, $join);

    if (!empty($params['ticket_id'])) {
        if (!is_array($params['ticket_id'])) {
            $params['ticket_id'] = explode(',', $params['ticket_id'] );
        }
        $condition .= db_quote(" AND t.ticket_id in (?a)", $params['ticket_id']);
        unset($params['page']);
    }

    $fields = ' t.*, mb.mailbox_name ';
    $join['helpdesk_mailboxes'] = " LEFT JOIN ?:helpdesk_mailboxes AS mb ON t.mailbox_id = mb.mailbox_id";

    if (Registry::get('runtime.company_id')) {
        $condition .= fn_get_company_condition('mb.company_id');
    }

    if (!isset($params['ticket_id'])) {
        $fields .= ' , max(m.timestamp) as updated';
        $join['helpdesk_messages'] = db_quote(" LEFT JOIN ?:helpdesk_messages AS m on t.ticket_id = m.ticket_id");
        $order = ' ORDER BY updated DESC ';
    }

    if (!empty($params['status'])) {
        $days_threshold = Registry::get('addons.helpdesk.days_threshold') ? Registry::get('addons.helpdesk.days_threshold') : 0;
        $condition .= db_quote(" AND ( m.status = ?s OR (m.status = 'W' AND m.timestamp < ?i))", $params['status'], (TIME - SECONDS_IN_DAY * $days_threshold));
    }

    if (!empty($params['user_id'])) {
        $join['helpdesk_ticket_users'] = " LEFT JOIN ?:helpdesk_ticket_users AS tu ON tu.ticket_id = t.ticket_id";
        $condition .= db_quote(" AND tu.user_id = ?i", $params['user_id']);
    } elseif (AREA == 'C') {
        if (!empty(Tygh::$app['session']['auth']['ticket_ids'])) {
            $condition .= db_quote(" AND t.ticket_id IN (?a)", Tygh::$app['session']['auth']['ticket_ids']);
        } else {
            //SECURITY REASON
            return array([], $params);
        }
    }

    if (isset($params['mailbox_id'])) {
        $condition .= db_quote(" AND t.mailbox_id = ?i", $params['mailbox_id']);
    }

    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT count(DISTINCT(t.ticket_id)) FROM ?:helpdesk_tickets AS t " . implode(' ', $join) . " WHERE $condition");
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $tickets = db_get_hash_array("SELECT $fields FROM ?:helpdesk_tickets AS t" . implode(' ', $join) . " WHERE $condition $group $order $limit", 'ticket_id');

    if (!isset($params['ticket_id']) && !empty($tickets)) {
        $ticket_count_new = db_get_hash_array("SELECT COUNT(m.message_id) as count_new, m.ticket_id FROM ?:helpdesk_messages AS m WHERE m.status = ?s AND m.ticket_id in (?a) GROUP BY m.ticket_id", 'ticket_id', 'N', array_keys($tickets));
        $ticket_count_all = db_get_hash_array("SELECT COUNT(m.message_id) as count_all, m.ticket_id FROM ?:helpdesk_messages AS m WHERE m.ticket_id in (?a) GROUP BY m.ticket_id", 'ticket_id', array_keys($tickets));
        $ticket_count_unviewed = db_get_hash_array("SELECT COUNT(m.message_id) as count_unviewed, m.ticket_id FROM ?:helpdesk_messages AS m WHERE m.viewed = ?s AND m.ticket_id in (?a) GROUP BY m.ticket_id", 'ticket_id', 'N', array_keys($tickets));
        if ($ticket_count_unviewed) $params['has_unviewed'] = true;

        $tickets = fn_array_merge($tickets, $ticket_count_new, $ticket_count_all, $ticket_count_unviewed);

        foreach ($tickets as $ticket_id => &$ticket) {
            $ticket['users'] = fn_get_ticket_users(['ticket_id' => $ticket_id]);
        }
    }

    return array($tickets, $params);
}

function fn_update_ticket($data, $ticket_id = 0) {
    $ticket_users = array();
    if (!is_array($data['users'])) {
        $data['users'] = explode(',', $data['users']);
    }

    if (!empty($ticket_id)) {
        db_query('UPDATE ?:helpdesk_tickets SET ?u WHERE ticket_id = ?i', $data, $ticket_id);
        db_query('DELETE FROM ?:helpdesk_ticket_users WHERE ticket_id = ?i', $ticket_id);
    } else {
        $mailbox_admin = db_get_field('SELECT responsible_admin FROM ?:helpdesk_mailboxes WHERE mailbox_id = ?i', $data['mailbox_id']);
        if ($mailbox_admin) {
            $data['users'] = array_merge($data['users'], explode(',', $mailbox_admin));
        }
        fn_set_hook('update_ticket_pre', $data);

        $ticket_id = db_query("INSERT INTO ?:helpdesk_tickets ?e", $data);
    }

    $ticket_users['ticket_id'] = $ticket_id;

    if (!empty($data['users'])) {
        foreach ($data['users'] as $ticket_users['user_id']) {
            db_query("REPLACE INTO ?:helpdesk_ticket_users ?e", $ticket_users);
        }
    }

    return $ticket_id;
}

function fn_get_ticket($params, $items_per_page = 10) {
    // if (empty($params['ticket_id'])) {
    //     return array([], $params);
    // }
    $tickets_params = $params;
    unset($tickets_params['page'], $tickets_params['items_per_page']);
    list($tickets) = fn_get_tickets($tickets_params);

    if (!isset($params['ticket_id']) && empty($tickets)) {
        // need to create a new ticket for new user
        $ticket_data = array(
            'subject' => __('helpdesk'),
            'mailbox_id' => reset(fn_get_mailboxes())['mailbox_id'],
            'users' => [Tygh::$app['session']['auth']['user_id']]
        );
        if (empty($ticket_data['mailbox_id'])) {
            return false;
        }
        $params['ticket_id'] = fn_update_ticket($ticket_data, 0);
        list($tickets) = fn_get_tickets($params);
    }

    $ticket = reset($tickets);
    if (!empty($tickets)) {
        $ticket['users'] = fn_get_ticket_users(['ticket_id' => array_keys($tickets)]);
        if (!YesNo::toBool(Registry::get('addons.helpdesk.ticketing_system'))) {
            $ticket['subject'] = __('helpdesk');
        }
        if (isset($params['get_messages'])) {
            $params['ticket_id'] = array_keys($tickets);
            list($ticket['messages'], $params) = fn_get_messages($params, $items_per_page);

            if (SiteArea::isStorefront(AREA)) {
                $messages = array_filter($ticket['messages'], function($v) {return $v['viewed'] == 'N';});
                if (!empty($messages)) db_query('UPDATE ?:helpdesk_messages SET `viewed` = ?s WHERE message_id IN (?a)', 'Y', array_keys($messages));
            }
        }
    }

    return array($ticket, $params);
}

function fn_get_messages($params, $items_per_page = 10) {
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    $limit = '';

    $condition = '1';

    if (isset($params['ticket_id'])) {
        if (!is_array($params['ticket_id'])) {
            $params['ticket_id'] = [$params['ticket_id']];
        }
        $condition .= db_quote(' AND ticket_id IN (?a)', $params['ticket_id']);
    }

    if (isset($params['message_id'])) {
        $condition .= db_quote(' AND m.message_id = ?i', $params['message_id']);    
    }

    if (!empty($params['notified'])) {
        if (is_array($params['notified'])) {
            $condition .= db_quote(" AND notified in (?a)", $params['notified']);
        } else {
            $condition .= db_quote(" AND notified = ?s", $params['notified']);  
        }
    }

    if (isset($params['q'])) {
        $params['q'] = trim($params['q']);
        $pieces = fn_explode(' ', $params['q']);
        foreach ($pieces as $piece) {
            $tmp[] = db_quote(" m.message LIKE ?l", '%' . $piece . '%');
        }
        $condition .= ' AND (' . implode(' AND ', $tmp) . ') ';
    }

    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:helpdesk_messages AS m WHERE $condition");
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $sort = db_sort($params, ['timestamp' => 'timestamp'], 'timestamp', 'desc');


    $messages = db_get_hash_array("SELECT m.*, CONCAT(u.firstname, ' ', u.lastname) as user FROM ?:helpdesk_messages AS m LEFT JOIN ?:users AS u ON u.user_id = m.user_id  WHERE $condition GROUP BY message_id ?p $limit ", 'message_id', $sort);

    foreach ($messages as $message_id => &$message) {
        if (!isset($message['status'])) {
            $message['status'] = 'C';
        }
        
        if (AREA == 'A' && $params['hide_blockquote']) {
            $onclick = "\$(this).removeClass('hidden-text');";
            $message['message'] = preg_replace("'<blockquote'","<blockquote class='hidden-text' onclick=" . $onclick . "", $message['message'], 1, $count);
        }
        
    }

    foreach ($messages as &$message) {
        $files = db_get_array("SELECT file_id, filename FROM ?:helpdesk_message_files WHERE message_id = ?i", $message['message_id']);
        if (!empty($files)) {
            $message['files'] = $files;
        }
    }

    return array($messages, $params);
}

function fn_get_ticket_users($params) {
    $users = array();
    if (isset($params['ticket_id']) && !empty($params['ticket_id'])) {    
        $join = 'LEFT JOIN ?:helpdesk_ticket_users AS tu ON u.user_id = tu.user_id';

        if (!empty($params['ticket_id']) && !is_array($params['ticket_id'])) {
            $params['ticket_id'] = [$params['ticket_id']];
        }

        $condition = db_quote('tu.ticket_id IN (?a)', $params['ticket_id']);
        $fields = "u.email, u.user_id, u.firstname, u.lastname, CONCAT(u.firstname, ' ', u.lastname) AS username";

        if (!empty($params['user_type'])) {
            $condition .= db_quote(" AND u.user_type in (?a)", $params['user_type']);
        }
        if (!empty($params['notification'])) {
            $condition .= db_quote(' AND u.helpdesk_notification = ?s', $params['notification']);
        }

        $users = db_get_hash_array("SELECT $fields FROM ?:users AS u $join WHERE $condition", 'user_id');
    }

    return $users;
}

function fn_update_message(&$data, $message_id = 0) {

    $uploaded_data = fn_filter_uploaded_data('ticket_data');
    if ($uploaded_data)
    foreach ($uploaded_data as $key => $file) {
        $data['attachment'][$file['name']] = file_get_contents($file['path']);
    }

    $message = &$data['message'];
    unset($data['message_id']);
    //str_replace(array('<p>', '</p>'), array('', '<br>'), '\\1');

    /*$message = preg_replace("/\[quote](.+)\[\/quote]/Use", "'[quote]' . str_replace(array('</p><p>', '<p>', '</p>'), array('','', '<br>'), trim('\\1')) . '[/quote]'", $message);
    $message = preg_replace("/\[q](.+)\[\/q]/Use", "'[q]' . str_replace(array('</p><p>', '<p>', '</p>'), array('','', '<br>'), trim('\\1')) . '[/q]'", $message);*/
    $blockquote = '<blockquote style="background-color:#eeeeee;border:1px solid silver;padding:10px 15px">';
    $message = str_replace(array('[quote]<br>','[quote]', '[/quote]'), array($blockquote, $blockquote, '</blockquote>'), $message);
    $message = str_replace(array('[q]<br>','[q]', '[/q]'), array($blockquote, $blockquote, '</blockquote>'), $message);

    if (!empty($message_id)) {
        db_query('UPDATE ?:helpdesk_messages SET ?u WHERE message_id = ?i', $data, $message_id);
    } else {
        if (!isset($data['status']) && Tygh::$app['session']['auth']['user_type'] == 'A') {
            $data['status'] = 'C';
        }

        $data['user_id'] = !empty($data['user_id']) ? $data['user_id'] : Tygh::$app['session']['auth']['user_id'];
        $data['timestamp'] = (!empty($data['timestamp']))? $data['timestamp'] : time();
        
        $message_id = $data['message_id'] = db_query("INSERT INTO ?:helpdesk_messages ?e", $data);

        if (!empty($data['attachment'])) {
            fn_add_attachment($data);
        }
    }
    return $message_id;
}

function fn_add_attachment($data) {

    foreach ($data['attachment'] as $file_name => $file_content) {
        $files[$file_name]['filename'] = $file_name;
        $files[$file_name]['file_path'] = $data['ticket_id'] . "/" . $data['message_id'] . "/". $file_name;
        list($filesize, $filename) = Storage::instance('helpdesk_files')->put($files[$file_name]['file_path'], array(
             'contents' => $file_content
        ));
        $files[$file_name]['file_size'] = $filesize;
        $files[$file_name]['message_id'] = $data['message_id'];
    }

    foreach ($files as $file) {
        db_query('INSERT INTO ?:helpdesk_message_files ?e', $file);
    }
}

function fn_delete_ticket($ticket_id) {
    $res = db_query("DELETE FROM ?:helpdesk_tickets WHERE ticket_id = ?i", $ticket_id);
    db_query("DELETE FROM ?:helpdesk_ticket_users WHERE ticket_id = ?i", $ticket_id);
    $messages = db_get_fields('SELECT message_id FROM ?:helpdesk_messages WHERE ticket_id = ?i', $ticket_id);
    foreach ($messages as $message_id) {
        fn_delete_helpdesk_message($message_id);
    }
}

function fn_delete_helpdesk_message($message_id) {
    $ticket_id = db_get_field("SELECT ticket_id FROM ?:helpdesk_messages WHERE message_id = ?i", $message_id);
    db_query("DELETE FROM ?:helpdesk_messages WHERE message_id = ?i", $message_id);
    $files = db_get_fields("SELECT filename FROM ?:helpdesk_message_files WHERE message_id = ?i", $message_id);
    foreach ($files as $filename) {
        Storage::instance('helpdesk_files')->delete($ticket_id . '/' . $filename);
    }
    db_query("DELETE FROM ?:helpdesk_message_files WHERE message_id = ?i", $message_id);
    return true;
}

function fn_get_helpdesk_file($params) {
    $file = db_get_row("SELECT f.*, m.ticket_id FROM ?:helpdesk_message_files AS f LEFT JOIN ?:helpdesk_messages AS m ON f.message_id = m.message_id WHERE file_id = ?i", $params['file_id']);
    Storage::instance('helpdesk_files')->get($file['ticket_id'] . '/' . $file['message_id'] . '/' . $file['filename']);
}

function fn_helpdesk_get_mail() {
    $mailboxes = fn_get_mailboxes();
    $i = 0;
    $mails = array();
    foreach ($mailboxes as $settings) {
        $mail_reader = Tygh::$app['addons.helpdesk.mail_reader'];
        $mail_reader->setSettings(['host' => "{" . $settings['host'] . "}", 'login' => $settings['email'], 'password' => $settings['password'] ] );
        if ($mail_reader) {
            $mails = $mail_reader->getMail();
            if (!empty($mails)) {
                foreach ($mails as $mail) {
                    if (db_get_field('SELECT status FROM ?:users WHERE email = ?s', $mail['from']) == 'D') {
                        continue;
                    }

                    $i++;
                    $user_id = db_get_field('SELECT user_id FROM ?:users WHERE email = ?s', $mail['from']);

                    if (empty($user_id)) {
                        $user_data['email'] = $mail['from'];
                        $uname = explode(' ', $mail['name']);

                        $user_data['firstname'] = $uname[0];
                        $user_data['lastname'] = $uname[1] ? $uname[1] : ' ';
                        list($user_id, ) = fn_update_user(0, $user_data, Tygh::$app['session']['auth'], 'N', 'Y');
                    }

                    $prefix = $settings['ticket_prefix'];

                    preg_match('/\[' . $prefix . '_TID:\D?(\d+).*\]/', $mail['subject'], $ticket_id);

                    if (!empty($ticket_id)) $ticket_id = db_get_field('SELECT ticket_id FROM ?:helpdesk_tickets WHERE ticket_id = ?i', $ticket_id[1]);

                    if (!$ticket_id) {
                        $ticket_id = db_get_field('SELECT ?:helpdesk_tickets.ticket_id FROM ?:helpdesk_tickets LEFT JOIN ?:helpdesk_messages ON ?:helpdesk_tickets.ticket_id = ?:helpdesk_messages.ticket_id WHERE subject = ?s AND user_id = ?i ORDER BY timestamp DESC', $mail['subject'], $user_id);
                    }

                    if (!$ticket_id) {
                        $ticket_data = array(
                            'subject' => $mail['subject'],
                            'mailbox_id' => $settings['mailbox_id'],
                            'users' => [$user_id]
                        );
                        $ticket_id = fn_update_ticket($ticket_data, 0);
                    }

                    $message = [
                        'ticket_id' => $ticket_id,
                        'user_id' => $user_id,
                        'message' => (!empty($mail['html'])) ? fn_normalize_html_content($mail['html']) : $mail['plain'],
                        'timestamp' => $mail['timestamp'],
                        'attachment' => $mail['attach'],
                        'status' => 'N',
                    ];

                    $message['message'] = mb_convert_encoding($message['message'], 'UTF-8', $mail['charset']);

                    if (!empty($message['message'])) fn_update_message($message);
                }
            }
        }
    }

    if (!defined('CONSOLE')) {
        fn_print_r("Received " . $i . " messages");
    }
}

function fn_helpdesk_send_mail() {
    $mailboxes = fn_get_mailboxes();
    $messages = db_get_array('SELECT m.*, t.subject, t.mailbox_id, u.user_type FROM ?:helpdesk_messages AS m LEFT JOIN ?:helpdesk_tickets AS t ON t.ticket_id = m.ticket_id LEFT JOIN ?:users AS u ON u.user_id = m.user_id WHERE notified = ?s LIMIT 300', 'N');
    foreach ($messages as &$message) {
        if ($message['user_type'] == 'C') {
            $user_type_condition = db_quote(" AND u.user_type != ?s", 'C');
        } else {
            $user_type_condition = db_quote(" AND u.user_type = ?s", 'C');
        }
        $message['users'] = fn_get_ticket_users(array('ticket_id' => $message['ticket_id'], 'user_type' => ($message['user_type'] == 'C') ? ['A', 'V'] : 'C', 'notification' => 'Y'));
    }

    $mailer = Tygh::$app['mailer'];

    foreach ($messages as &$message) {
        if (!empty($message['users'])) {
            $notified = false;
            $settings = $mailboxes[$message['mailbox_id']];

            $tid = '[' . $settings['ticket_prefix'] . '_TID:' . $message['ticket_id']. ']';
            $message['subject'] = __('helpdesk') . ' ' . $settings['mailbox_name'];

            Tygh::$app['view']->assign('message', $message['message']);
            Tygh::$app['view']->assign('tid', $tid);
            $subj = 'addons/helpdesk/subject.tpl';
            $body = 'addons/helpdesk/message.tpl';

            $attachements = db_get_fields('SELECT filename FROM ?:helpdesk_message_files WHERE message_id = ?i', $message['message_id']);

            if (!empty($attachements)) {
                foreach ($attachements as &$file_path) {
                    $file_path = 'var/helpdesk_files/' . $message['ticket_id'] . '/' . $message['message_id'] . '/' . $file_path;                  
                }
            }

            $mailbox_email_settings = array(
                'mailer_send_method' => 'smtp',
                'mailer_smtp_host' => $settings['smtp_server'],
                'mailer_smtp_username' => $settings['email'],
                'mailer_smtp_password' => $settings['password'],
                'mailer_smtp_auth' => 'Y'
            );
            $mailer_settings = fn_array_merge(Registry::get('settings.Emails'), $mailbox_email_settings);
            
            fn_set_hook('helpdesk_send_message_pre', $notified, $message, $mailboxes[$message['mailbox_id']]);

            if (!$notified) {
                $to = array_filter(filter_var_array(array_column($message['users'], 'email'), FILTER_VALIDATE_EMAIL));
                if (!empty($to)) {
                    $notified = $mailer->send(array(
                        'to' => array_column($message['users'], 'email'),
                        'from' => array('name' => $mailboxes[$message['mailbox_id']]['mailbox_name'], 'email' => $settings['email']),
                        'tpl' => $body,
                        'is_html' => true,
                        'mailbox_id' => $message['mailbox_id'],
                        'data' => array(
                            'subject' => $tid . ' ' . $message['subject']
                            ),
                        'attachments' => $attachements,
                    ), 'A', Registry::get('settings.Appearance.backend_default_language'), $mailer_settings);
                } else {
                    $notified = true;
                }
            }
        } else {
            $notified = true;
        }
        if ($notified) {
            db_query('UPDATE ?:helpdesk_messages set notified = "Y" WHERE message_id = ?i', $message['message_id']);
        }
    }

    if (!defined('CONSOLE')) {
        fn_print_r('done');
    }
}

function fn_normalize_html_content($content) {
    $content = preg_replace ("/<style[^>]*?>.*?<\\/style>/si",'',$content);
    $content = preg_replace ("/(style=\".*?\")/si",'',$content);
    $content = strip_tags($content, '<div><span><a><p><br><blockquote><b><font>');
    return $content;
}

function fn_helpdesk_can_user_access_ticket($ticket_id, array $auth) {
    $can_access = false;
    if (empty($auth['user_id']))  {
        if (isset($auth['ticket_ids']) && in_array($ticket_id, $auth['ticket_ids'])) {
            $can_access = true;
        }
        return $can_access;
    }

    if (AREA === 'C' || fn_is_restricted_admin(db_get_row("SELECT user_id, is_root, company_id FROM ?:users WHERE user_id = ?i", $auth['user_id']))) {
        $can_access = (bool) db_get_field(
            'SELECT ticket_id FROM ?:helpdesk_ticket_users WHERE user_id = ?i AND ticket_id = ?i',
            $auth['user_id'],
            $ticket_id
        );
    } elseif ($auth['user_type'] == 'A') {
        // replace by UserTypes::ADMIN in 2022
        $can_access = true;
    }

    return $can_access;
}

/* ======================= HOOKS ======================= */
function fn_helpdesk_get_predefined_statuses($type, &$statuses) {
    if ($type == 'helpdesk') {
        $statuses['helpdesk'] = array(
            'N' => __('new'),
            'C' => __('closed'),
            'W' => __('waiting')
        );
    }
}
