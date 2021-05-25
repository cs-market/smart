<?php

namespace Tygh\Addons\UsergroupNotifications;

use Tygh\Tygh;
use Tygh\Core\BootstrapInterface;
use Tygh\Core\HookHandlerProviderInterface;
use Tygh\Core\ApplicationInterface;

class Bootstrap implements BootstrapInterface, HookHandlerProviderInterface
{
    /**
     * @inheritDoc
     */
    public function boot(ApplicationInterface $app)
    {
    }

    public function getHookHandlerMap()
    {
        return [
            'change_usergroup_status' => ['Tygh\Addons\UsergroupNotifications\Bootstrap', 'onUpdateStatus'],
            'get_usergroups' => ['Tygh\Addons\UsergroupNotifications\Bootstrap', 'onGetUsergroups']
        ];
    }

    static public function onUpdateStatus($result, $data, $force_notification) {
        if ($data['status'] == 'A') {
            $usergroup_id = $data['usergroup_id'];
            $usergroups = fn_get_usergroups(array('usergroup_id' => $usergroup_id), DESCR_SL);
            $usergroup = $usergroups[$usergroup_id];
            $user = fn_get_user_short_info($data['user_id']);
            if (!empty(trim(strip_tags($usergroup['email_template'])))) {
                $mailer = Tygh::$app['mailer'];

                $mailer->send(array(
                    'to' => $user['email'],
                    'from' => 'default_company_users_department',
                    'data' => array(
                        'message' => $usergroup['email_template'],
                        'subject' => __("usergroup_activated")
                    ),
                    'tpl' => 'addons/usergroup_notifications/message.tpl',
                    'company_id' => $user['company_id'],
                ), 'A', $lang_code);
            }
        }
    }

    static public function onGetUsergroups($params, $lang_code, &$field_list, $join, $condition, $group_by, $order_by, $limit) {
        $field_list .= ', b.email_template';
    }
}
