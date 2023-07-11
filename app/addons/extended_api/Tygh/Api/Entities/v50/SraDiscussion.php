<?php

namespace Tygh\Api\Entities\v50;

use Tygh\Api\Entities\v40\SraDiscussion as BaseSraDiscussion;
use Tygh\Enum\Addons\Discussion\DiscussionObjectTypes;
use Tygh\Enum\Addons\Discussion\DiscussionTypes;
use Tygh\Enum\NotificationSeverity;

class SraDiscussion extends BaseSraDiscussion
{
    public function create($params) {
        $_data = [
            'object_id'   => $params['object_id'],
            'object_type' => DiscussionObjectTypes::ORDER,
            'type'        => DiscussionTypes::TYPE_COMMUNICATION_AND_RATING,
            'company_id'  => empty($this->auth['company_id']) ? 0 : $this->auth['company_id']
        ];

        if (fn_discussion_check_thread_permissions($_data, $this->auth)) {
            $discussion = fn_get_discussion($params['object_id'], DiscussionObjectTypes::ORDER);

            if (!empty($discussion['thread_id'])) {
                db_query('UPDATE ?:discussion SET ?u WHERE thread_id = ?i', $_data, $discussion['thread_id']);
            } else {
                $discussion['thread_id'] = db_replace_into('discussion', $_data);
            }
        } else {
            fn_set_notification(NotificationSeverity::ERROR, __('error'), __('cant_find_thread'));
        }
        if ($discussion['thread_id']) {
            $params['name'] = fn_get_user_name($this->auth['user_id']);
            $params['thread_id'] = $discussion['thread_id'];            
        }

        return parent::create($params);
    }
}
