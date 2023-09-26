<?php

use Tygh\Enum\NotificationSeverity;
use Tygh\Settings;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode=='update' && $_REQUEST['addon'] == 'telegram' && $action == 'register') {
        $messenger = Tygh::$app['addons.telegram.messenger'];
        $response = $messenger->send('setWebhook', ['url' => str_replace('index.php', '', fn_url('', 'C', 'https')).'api/telegram']);
        if ($response->isSuccess()) {
            fn_set_notification(NotificationSeverity::NOTICE, __('notice'), reset($response->getMessages()));
        }
        $response = $messenger->send('getMe');
        $data = $response->getData();
        Settings::instance()->updateValue('bot_name', $data['result']['username'], 'telegram', false, false);
    }

    return array(CONTROLLER_STATUS_OK);
}
