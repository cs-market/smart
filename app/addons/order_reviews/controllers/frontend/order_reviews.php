<?php

use Tygh\Enum\Addons\Discussion\DiscussionObjectTypes;
use Tygh\Enum\Addons\Discussion\DiscussionTypes;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_data = isset($_REQUEST['post_data']) ? $_REQUEST['post_data'] : array();

    if ($mode == 'add' && !empty($post_data['object_id'])) {
        $auth = Tygh::$app['session']['auth'];
        $_data = [
            'object_id'   => $post_data['object_id'],
            'object_type' => DiscussionObjectTypes::ORDER,
            'type'        => DiscussionTypes::TYPE_COMMUNICATION_AND_RATING,
            'company_id'  => empty($post_data['company_id']) ? 0 : $post_data['company_id']
        ];

        if (fn_discussion_check_thread_permissions($_data, $auth)) {

            $discussion = fn_get_discussion($_REQUEST['object_id'], DiscussionObjectTypes::ORDER);

            if (!empty($discussion['thread_id'])) {
                db_query('UPDATE ?:discussion SET ?u WHERE thread_id = ?i', $_data, $discussion['thread_id']);
            } else {
                if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
                    $_data['company_id'] = Registry::get('runtime.company_id');
                }
                $discussion['thread_id'] = db_replace_into('discussion', $_data);
            }
        } else {
            fn_set_notification(NotificationSeverity::ERROR, __('error'), __('cant_find_thread'));
        }
        if ($discussion['thread_id']) {
            $post_data['name'] = fn_get_user_name($auth['user_id']);
            $post_data['thread_id'] = $discussion['thread_id'];
            fn_add_discussion_post($post_data, true);
        }
    }
}
