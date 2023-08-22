<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

/*hooks*/
/** Call request send notice
 */
function fn_sw_telegram_call_requests_do_call_request_post($params, $product_data, $cart, $auth, $result)
{
    $addon_settings = Registry::get('addons.sw_telegram');

    $site = Registry::get('config.current_host');
	$message = "";
    fn_sw_telegram_coustruct_tg_message($message, $site);

    if (isset($params['order_id'])) {
        fn_sw_telegram_coustruct_tg_message($message, __('call_requests.buy_now_with_one_click'));
        fn_sw_telegram_coustruct_tg_message($message, __('tg_order_num_text') . ' ' . $params['order_id']);
    } else {
        fn_sw_telegram_coustruct_tg_message($message, __('sw_telegrame_call_order'));
    }

    if (isset($params['order_id'])) {
        $order_status = fn_sw_telegram_get_order_info_status($params['order_id']);
        fn_sw_telegram_coustruct_tg_message($message, __('order_status') . ': <b>' . $order_status  . "</b>");
    }

    $product_id = isset($params['product_id']) ? $params['product_id'] : false;

    if ($product_id != false) {
        $product_data_full = fn_get_product_data($product_id, $auth);

        if (fn_allowed_for('MULTIVENDOR')) {
            fn_sw_telegram_coustruct_tg_message($message, __('vendor') . ': ' . fn_get_company_name($product_data_full['company_id']));
        }
    }

    if (isset($params['name'])) {
        fn_sw_telegram_coustruct_tg_message($message, '');
        fn_sw_telegram_coustruct_tg_message($message, __('first_name') . ': ' . $params['name']);
    }

    if ($auth['user_id'] != false) {
        $user_name = fn_get_user_name($auth['user_id']);
        fn_sw_telegram_coustruct_tg_message($message, __('signed_in_as') . ': ' . $user_name);
        fn_sw_telegram_coustruct_tg_message($message, __('user_id') . ': ' . $auth['user_id']);
    }

    if (isset($params['email']) && !empty($params['email'])) {
        fn_sw_telegram_coustruct_tg_message($message, __('email') . ': ' . $params['email']);
    }

    if (isset($params['phone'])) {
        fn_sw_telegram_coustruct_tg_message($message, __('phone') . ': ' . $params['phone']);
    }

    if (isset($params['time_from'])) {
        fn_sw_telegram_coustruct_tg_message($message, __('call_requests.convenient_time') . ': ' . $params['time_from'] . " - " . $params['time_to']);
    }

    if (isset($params['product_id'])) {
        $product_name = $product_data_full['product'];

        fn_sw_telegram_coustruct_tg_message($message, __('call_requests.requested_product'));
        fn_sw_telegram_coustruct_tg_message($message, $product_name);
    }

    if (!empty($product_data_full['product_code'])) {
        fn_sw_telegram_coustruct_tg_message($message, __('sku') . ': ' . $product_data_full['product_code']);
    }

    if (!isset($params['order_id']) && $addon_settings['tg_call_notification'] == "Y") {
        fn_sw_telegram_send_tg('sendMessage', '', $message);
    }

    if (
        fn_allowed_for('MULTIVENDOR')
        && $addon_settings['tg_call_vendor_notification'] == "Y"
    ) {
        $vendor_chats = fn_sw_telegram_get_vendor_chats($product_data['company_id']);
        if (!empty($vendor_chats)) {
            foreach ($vendor_chats as $vendor_chat_id) {

                if (empty($vendor_chat_id) || $vendor_chat_id == false) {
                    continue;
                }

                fn_sw_telegram_send_tg('sendMessage', trim($vendor_chat_id), $message);
            }
        }
    }
}

/** Discussion send notice
 */
function fn_sw_telegram_add_discussion_post_post($post_data, $send_notifications)
{

    $tg_rewiew_notification = Registry::get('addons.sw_telegram.tg_rewiev_notification');
    $tg_review_vendor_notification = Registry::get('addons.sw_telegram.tg_review_vendor_notification');

    if (AREA == 'C') {
        $site = Registry::get('config.current_host');

        $post_disqus = __('sw_telegrame_get_new_post_disqus');
        $rating_value_stars = __('sw_telegrame_rating_value_stars');

        $message = $post_disqus . "\r\n";


        $product_id = db_get_field('SELECT object_id FROM ?:discussion WHERE thread_id = ?i', $post_data['thread_id']);

        $product_data = fn_get_product_data($product_id, $_SESSION['auth']);
        $message .= __('product') . ': ' . $product_data['product'] . "\r\n";

        if (!empty($product_data['product_code'])) {
            $message .= __('sku') . ': ' . $product_data['product_code'] . "\r\n";
        }


        if (fn_allowed_for('MULTIVENDOR')) {
            $message .= __('vendor') . ': ' . fn_get_company_name($product_data['company_id']) . "\r\n";
        }

        if (isset($post_data['name'])) {
            $message .= __('first_name') . ': ' . $post_data['name'] . "\r\n";
        }

        if (isset($post_data['rating_value'])) {
            $message .= $rating_value_stars . ' ' . $post_data['rating_value'] . "\r\n";
        }

        if (isset($post_data['message'])) {
            $message .= __('message') . ': ' . $post_data['message'] . "\r\n";
        }

        if (isset($_REQUEST['redirect_url'])) {
            $message .=  __('url') . ': ' . $_SERVER['HTTP_REFERER'] . "\r\n";
        }

        if (isset($post_data['email'])) {
            $message .= __('email_user') . ': ' . $post_data['email_user'] . "\r\n";
        }

        if ($tg_rewiew_notification == "Y") {
            fn_sw_telegram_send_tg('sendMessage', $chat_id = '', $message);
        }

        if (
            fn_allowed_for('MULTIVENDOR')
            && $tg_review_vendor_notification == "Y"
        ) {

            $params_stick = array(
                'send_stiker' => true,
                'sticker' => REVIEW_STICKER,
                'parse_mode' => 'json'
            );
            $vendor_chats = fn_sw_telegram_get_vendor_chats($product_data['company_id'], $post_data['user_id']);
            if (!empty($vendor_chats)) {
                foreach ($vendor_chats as $vendor_chat_id) {

                    if (empty($vendor_chat_id) || $vendor_chat_id == false) {
                        continue;
                    }

                    fn_sw_telegram_send_tg('sendSticker', trim($vendor_chat_id), '', $params_stick);
                    fn_sw_telegram_send_tg('sendMessage', trim($vendor_chat_id), $message);
                }
            }
        }
    }
}

/** Place order send notice
 */
function fn_sw_telegram_place_order($order_id, $action, $order_status, $cart, $auth)
{

    $addon_settings = Registry::get('addons.sw_telegram');
    $order = fn_get_order_info($order_id);

    if (
        AREA == 'C'
        && $order['is_parent_order'] == 'N'
        && $addon_settings['tg_order_notification'] == 'Y'
    ) {

        $currency_settings = Registry::get('currencies.' . CART_PRIMARY_CURRENCY);

        $is_processor_script = false;
        if ($action != 'save') {
            list($is_processor_script,) = fn_check_processor_script($cart['payment_id'], true);
        }

        if (!$is_processor_script && $order_status == STATUS_INCOMPLETED_ORDER) {
            $order_status = 'O';
        }

        $order['order_status'] = $order_status;
        $message = '';
        $message = fn_sw_telegram_get_order_message($message, $order, $auth);

        $params = array(
            'send_stiker' => true,
            'sticker' => ORDER_STICKER
        );

        $chat_id_user = fn_sw_telegram_get_chat_id_user($order);
        if (!empty($chat_id_user)) {

            $user_firstname = $order['firstname'];
            if (empty($user_firstname)) {
                $user_firstname = !empty($order['s_firstname']) ? $order['s_firstname'] : $order['b_firstname'];
            }

            if (empty($user_firstname)) {
                $user_firstname = __('sw_telegram.dear_user');
            }

            $message_user = __('sw_telegram.user_message_order_pre', ["[name]" => $user_firstname]) . $message;
            fn_sw_telegram_send_tg('sendMessage', $chat_id_user, $message_user);
        }

        fn_sw_telegram_send_tg('sendMessage', $chat_id = '', $message, $params);

        if (
            fn_allowed_for('MULTIVENDOR')
            && $addon_settings['tg_allow_for_vendor'] == 'Y'
        ) {
            $vendor_chats = fn_sw_telegram_get_vendor_chats($order['company_id'], $order['user_id']);
            if (!empty($vendor_chats)) {
                foreach ($vendor_chats as $vendor_chat_id) {

                    if (empty($vendor_chat_id) || $vendor_chat_id == false) {
                        continue;
                    }

                    $message_pre = '';
                    if (!empty($vendor_chats_data['firstname'])) {
                        $message_pre = __('sw_telegram.vendor_message_order_pre', ["[firstname]" => $vendor_chats_data['firstname']]) . "\r\n";
                    }

                    $message = $message_pre . $message;

                    fn_sw_telegram_send_tg('sendSticker', trim($vendor_chat_id), '', $params);

                    fn_sw_telegram_send_tg('sendMessage', trim($vendor_chat_id), $message);
                }
            }
        }
    }
}

/** Change order status send notice
 */
function fn_sw_telegram_change_order_status($status_to, $status_from, $order_info, $force_notification, $order_statuses, $place_order)
{

    $addon_settings = Registry::get('addons.sw_telegram');
    $params = $_REQUEST;

    if ($status_to == 'N') {
        return;
    }

    if (isset($force_notification['T']) && $force_notification['T'] == true) {
        $params['notify_telegram'] = 'Y';
    }

    if (($addon_settings['tg_order_status_notification'] == 'Y'
        && isset($params['notify_telegram'])
        && $params['notify_telegram'] == 'Y')) {

        $order_data = $order_statuses[$status_to];

        $firstname = !empty($order_info['firstname']) ? $order_info['firstname'] : $order_info['s_firstname'];

        if (empty($firstname) && !empty($order_info['b_firstname'])) {
            $firstname = $order_info['b_firstname'];
        }

        if (empty($firstname)) {
            $firstname = __('sw_telegram.dear_user');
        }

        $message = __('sw_telegram.notify_message', array(
            '[order_id]' => $order_info['order_id'],
            '[name]' => $firstname,
            '[status]' => $order_data['description'],
            '[status_message]' => $order_data['email_header'],
        ));

        $chat_id = fn_sw_telegram_get_chat_id_user($order_info);

        if (!empty($chat_id)) {
            fn_sw_telegram_send_tg('sendMessage', $chat_id, $message);
        }
    }
}

/** Get order info
 */
function fn_sw_telegram_get_order_info(&$order, $additional_data)
{
    if (isset($order['order_id'])) {
        $order['status_order'] = fn_sw_telegram_get_status_order($order);
    }
}

/** MVE Edition
 */

/** Get user info hook
 */
function fn_sw_telegram_get_user_info($user_id, $get_profile, $profile_id, &$user_data)
{
    if (fn_allowed_for("MULTIVENDOR") && $user_data['user_type'] == 'V') {
        $user_data['allow_noty_tg'] = 'N';

        if (fn_sw_telegram_allow_for_tarif($user_data['company_id']) == true) {
            $user_data['allow_noty_tg'] = 'Y';
        }
    }
}
/** Get users hook
 */
function fn_sw_telegram_get_users($params, &$fields, $sortings, $condition, $join, $auth)
{
    if (fn_allowed_for("MULTIVENDOR")) {
        $fields[] = '?:users.chat_id';
        $fields[] = '?:users.noty_tg';
    }
}

//END hooks
