<?php

use Tygh\Registry;
use Tygh\Enum\YesNo;

defined('BOOTSTRAP') or die('Access denied');

function fn_telegram_api_get_user_data_pre($api, &$user_data) {
    $data = $api->getRequest()->getData();
    $chat_id = isset($data['callback_query']['message']['chat']['id']) ? $data['callback_query']['message']['chat']['id'] : $data['message']['chat']['id'];

    if (!empty($chat_id)) {
        // is root admins
        if ($chats = Registry::get('addons.telegram.chat_ids')) {
            $chats = explode(PHP_EOL, $chats);
            if (in_array($chat_id, $chats)) {
                $user_data = db_get_row('SELECT users.* FROM ?:users AS users WHERE is_root = ?s', 'Y', 'A', 'A');
            }
        }

        if (empty($user_data)) {
            $user_data = db_get_row('SELECT users.* FROM ?:users AS users WHERE chat_id = ?i', $chat_id);
        }
    }
}

function fn_telegram_fill_auth(&$auth, $user_data, $area, $original_auth) {
    if (defined('API') && isset($user_data['chat_id'])) {
        $auth['chat_id'] = $user_data['chat_id'];
    }
}

function fn_telegram_api_handle_request($api, &$authorized) {
    if (in_array($api->getRequest()->getResource(), ['telegram', 'telegram/'])) {
        $authorized = true;
    }
}

function fn_telegram_api_check_access($api, $entity, $method_name, &$can_access) {
    if ($entity instanceof Tygh\Api\Entities\Telegram && $method_name == 'create') {
        $can_access = true;
    }
}

function fn_telegram_place_order_post($cart, $auth, $action, $issuer_id, $parent_order_id, $order_id, $order_status, $short_order_data, $notification_rules) {
    if ($parent_order_id) return;

    $order = fn_get_order_info($order_id);

    $render_manager = Tygh::$app['addons.telegram.render_manager'];

    // notify root
    $settings = Registry::get('addons.telegram');
    if (YesNo::toBool($settings['tg_events_subscribed']) && !empty(trim($settings['chat_ids']))) {
        $chat_ids = explode(PHP_EOL, $settings['chat_ids']);
        array_walk($chat_ids, 'fn_trim_helper');

        foreach ($chat_ids as $chat_id) {
            $render_manager->initRender($auth, 'A', $chat_id);
            $data = $render_manager->renderLocation('/orders/'.$order_id, []);

            if (!empty($data)) {
                $messenger = \Tygh::$app['addons.telegram.messenger'];
                $result = $messenger->sendMessage($chat_id, $data);
            }
        }
    }

    // notify customer

    // notify vendors
    if (fn_allowed_for('MULTIVENDOR') && !empty($order['company_id']) && YesNo::toBool(db_get_field('SELECT tg_enabled FROM ?:companies WHERE company_id = ?i', $order['company_id']))) {
        $params = array(
            'company_id' => $order['company_id'],
            'status' => 'A',
            'user_type' => 'V',
            'tg_events_subscribed' => 'Y',
        );

        fn_set_hook('telegram_get_vendor_chats_get_params', $params, $order);

        list($vendor_admins,) = fn_get_users($params, $auth);

        if (!empty($vendor_admins)) {
            foreach ($vendor_admins as $vendor_admin) {
                $chat_id = $vendor_admin['chat_id'];
                $render_manager->initRender($auth, 'V', $chat_id);
                $data = $render_manager->renderLocation('/orders/'.$order_id, []);

                if (!empty($data)) {
                    $messenger = \Tygh::$app['addons.telegram.messenger'];
                    $result = $messenger->sendMessage($chat_id, $data);
                }
            }
        }
    }
}

function fn_telegram_get_users($params, &$fields, $sortings, &$condition, $join, $auth) {
    $fields['chat_id'] = '?:users.chat_id';
    if (isset($params['tg_events_subscribed'])) {
        $condition['tg_events_subscribed'] = db_quote(' AND ?:users.tg_events_subscribed = ?s AND ?:users.chat_id != ?s', $params['tg_events_subscribed'], '');
    }
}
