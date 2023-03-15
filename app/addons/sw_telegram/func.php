<?php

use Tygh\Registry;
use Tygh\Settings;
use Tygh\Mailer;
use Tygh\Http;
use Tygh\Addons\SwTelegram\HttpTgProxy;


if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

include_once(__DIR__ . '/sw.functions.php');
include_once(__DIR__ . '/hooks.php');

/** Funcion for add cols in tables
 * See EX variant fileds
 */
function fn_sw_telegram_for_install()
{
    /* EX. 
    products - table name
    field_name - field name => params field
    */

    $data = [
        'users' => [
            'chat_id' => "varchar(255) NOT NULL default ''",
            'noty_tg' => "CHAR(1) NOT NULL default 'Y'",
            'chat_id_timestamp' => "int(11) NOT NULL DEFAULT '0'"
        ],
        'orders' => [
            'chat_id' => "int(11) NOT NULL DEFAULT '0'"
        ]
    ];

    if (fn_allowed_for('MULTIVENDOR')) {
        $data['vendor_plans'] = [
            'noty_tg' => "CHAR(1) NOT NULL default 'N'"
        ];
    }

    fn_sw_telegram_add_table_cols($data);

    return;
}

/** Get info bot
 * @return array
 */
function fn_sw_telegram_get_info_tg()
{
    $addon_settings = Registry::get('addons.sw_telegram');
    $website = fn_sw_telegram_get_website_telegram($addon_settings, 'getMe');

    if ($addon_settings['use_proxy'] == 'Y') {
        $res = HttpTgProxy::sendMessageProxy($addon_settings, $website, []);
    } else {
        $res = Http::get($website, []);
    }

    return json_decode($res, true);
}

/** Get website
 * @param array $data
 */
function fn_sw_telegram_get_website_telegram($addon_settings, $metod)
{
    $botToken = $addon_settings['sw_bot_token'];
    $website = "https://api.telegram.org/bot" . $botToken . '/' . $metod;

    return $website;
}

/** Send message
 * @param string $metod
 * @param string $chat_id
 * @param string $message
 * @param string $params data
 * @param string $attachments
 */
function fn_sw_telegram_send_tg($metod = 'sendMessage', $chat_id = '', $message = '', $params = array(), $attachments = array())
{
    $addon_settings = Registry::get('addons.sw_telegram');
    $website = fn_sw_telegram_get_website_telegram($addon_settings, $metod);

    if (!empty($params) && !empty($chat_id)) {

        $request = array(
            'chat_id' => $chat_id,
            'text' => isset($params['text']) ? $params['text'] : '',
            'message' => $message,
            'parse_mode' => isset($params['parse_mode']) ? $params['parse_mode'] : 'html',
            'headers' => array(
                'Content-Type' => isset($params['parse_mode']) ? $params['parse_mode'] : 'html'
            ),
        );

        $request = array_merge($request, $params);
        $result = array();

        //SEND STICKER
        if ($metod == 'sendSticker' && $addon_settings['tg_send_stickers'] == 'Y') {
            if ($addon_settings['use_proxy'] == 'Y') {
                $result = HttpTgProxy::sendMessageProxy($addon_settings, $website, $request);
            } else {
                $result = Http::get($website, $request);
            }
        }

        $error = array(
            'result' => $result,
            'request' => $request,
            'params' => $params,
        );

        return !empty($result['result']) ? $result['result'] : array('error' => $result, 'res' => $error);
    }

    // if one chat
    if (!empty($chat_id)) {
        $data = array(
            'disable_web_page_preview' => true,
            'parse_mode' => 'html',
            'chat_id' => $chat_id,
            'text' => $message
        );

        if ($addon_settings['use_proxy'] == 'Y') {
            $res = HttpTgProxy::sendMessageProxy($addon_settings, $website, $data);
        } else {
            $res = Http::get($website, $data);
        }

        return json_decode($res, true);
    }

    //SEND ALL CHATS FROM SETTINGS
    $id_chats = $addon_settings['sw_id_chat'];
    if (!empty($id_chats)) {
        $id_chats_list = fn_explode(' ', $id_chats);

        foreach ($id_chats_list as $id_chat) {
            if (!empty($id_chat)) {
                if (isset($params['send_stiker'])) {
                    $params_sticker = array(
                        'chat_id' => $id_chat,
                        'sticker' => isset($params['stiker_id']) ? $params['stiker_id'] : ''
                    );
                    fn_sw_telegram_send_tg('sendSticker', $id_chat, '', $params_sticker);
                }
                fn_sw_telegram_send_tg($metod, $id_chat, $message);
            }
        }
    }

    return;
}

/** Send buttons
 * @param array $data
 */
function fn_sw_telegram_send_tg_buttons($content)
{
    $addon_settings = Registry::get('addons.sw_telegram');
    $website = fn_sw_telegram_get_website_telegram($addon_settings, 'sendMessage');

    if ($addon_settings['use_proxy'] == 'Y') {
        $res = HttpTgProxy::sendMessageProxy($addon_settings, $website, $content);
    } else {
        $res = Http::get($website, $content);
    }

    return;
}

/** Get chat id
 * @return int chat id
 */
function fn_sw_telegram_get_chat_id()
{
    $addon_settings = Registry::get('addons.sw_telegram');
    $id_chats = $addon_settings['sw_id_chat'];

    $get_chat_id = fn_sw_telegram_get_website_telegram($addon_settings, 'getUpdates');
    $set_web_hook = fn_sw_telegram_get_website_telegram($addon_settings, 'setWebhook');

    $data = array();

    if ($addon_settings['use_proxy'] == 'Y') {
        $array_result = HttpTgProxy::sendMessageProxy($addon_settings, $get_chat_id, $data);
    } else {
        $array_result = Http::get($get_chat_id, $data);
    }

    if (!empty($array_result)) {
        $array_result = (json_decode($array_result, true));

        if (!empty($array_result['error_code'])) {
            fn_set_notification('E', __('error'), $array_result['description']);

            if ($array_result['error_code'] == 409) {
                fn_sw_telegram_delete_webhook();
            }

            return;
        }
    } else {
        fn_set_notification('E', __('error'), 'Empty data');
        return;
    }

    if ($array_result == null) {
        fn_set_notification('E', __('error'), __('sw_telegram.hostinh_block'));
        return;
    }

    $chat_id = '';

    $result_chat_id = $array_result['result'];
    foreach ($result_chat_id as $results) {
        $message = $results['message'];
        $chat_id = $message['chat']['id'];
    }

    if (empty($chat_id)) {
        fn_set_notification('E', __('error'), __('sw_telegram.chat_is_empty'));
        return;
    }

    $message = __('sw_telegrame_your_chat_id') . " " . $chat_id;
    fn_set_notification('N', __('notice'), $message);

    $chats = Settings::instance()->getValue('sw_id_chat', 'sw_telegram');

    if (!empty($chats)) {

        $chats_expl = fn_explode(' ', $chats);

        foreach ($chats_expl as $chats_expl_id) {
            $chats_expl_id_elm = trim($chats_expl_id);

            $find_chat = false;

            if ($chats_expl_id_elm == $chat_id) {
                $find_chat = true;
                break;
            }
        }

        if ($find_chat == false) {
            $chats = $chats . ' ' . $chat_id;
        }
    } else {
        $chats = $chat_id;
    }

    Settings::instance()->updateValue('sw_id_chat', $chats, 'sw_telegram');

    if ($addon_settings['sw_bot_name'] == '') {
        $info = fn_sw_telegram_get_info_tg();
        if (isset($info['result']['username'])) {
            Settings::instance()->updateValue('sw_bot_name', $info['result']['username'], 'sw_telegram');
        }
    }
    return $chat_id;
}

/** Delete webhook
 */
function fn_sw_telegram_delete_webhook()
{
    $addon_settings = Registry::get('addons.sw_telegram');
    $web_hook = fn_sw_telegram_get_website_telegram($addon_settings, 'deleteWebhook');

    if ($addon_settings['use_proxy'] == 'Y') {
        $array_result = HttpTgProxy::sendMessageProxy($addon_settings, $web_hook, []);
    } else {
        $array_result = Http::get($web_hook, []);
    }

    if (!empty($array_result)) {
        $array_result = (json_decode($array_result, true));

        if (!empty($array_result['error_code'])) {
            fn_set_notification('E', __('error'), $array_result['description']);
        } else {
            fn_set_notification('N', __('notice'), __('sw_telegram.webhook_del'));
        }
    }

    return;
}

/** Get Start info
 */
function fn_sw_telegram_get_start()
{

    $addon_settings = Registry::get('addons.sw_telegram');
    $website = "https://api.telegram.org/bot" . $addon_settings['sw_bot_token'];
    $get_chat_id = fn_sw_telegram_get_website_telegram($addon_settings, 'getUpdates');

    $che = curl_init();
    $data = array(); // next dev

    if ($addon_settings['use_proxy'] == 'Y') {
        $array_result = HttpTgProxy::sendMessageProxy($addon_settings, $get_chat_id, $data);
    } else {
        $array_result = Http::get($get_chat_id, $data);
    }

    $results = $array_result['result'];

    foreach ($results as $result) {
        $message = $result['message'];
        $text_message = $message['text'];
    }

    if ($text_message == '/start') {
        file_get_contents($website . "/sendmessage?chat_id=" . $chat_id . "&text=ede");
    }

    return;
}

/** Get order text
 * @param string $message
 * @param array $order
 * @param array $auth
 * @param string $action (create|info)
 * @return string message text
 */
function fn_sw_telegram_get_order_message(&$message, $order, $auth, $action = 'create')
{

    if (empty($order)) {
        return $message;
    }

    $addon_settings = Registry::get('addons.sw_telegram');

    $order_id = $use_order_id = $order['order_id'];

    if ($addon_settings['ab__hoi_mask_id'] == 'Y') {
        $use_order_id = isset($order['ab__hoi_mask_id']) ? $order['ab__hoi_mask_id'] : $order_id;
    }

    $site = Registry::get('config.current_host');
    $currency_settings = Registry::get('currencies.' . CART_PRIMARY_CURRENCY);

    fn_sw_telegram_coustruct_tg_message($message, $site);

    if ($action == 'create') {
        fn_sw_telegram_coustruct_tg_message($message, __('sw_telegram.user_order_pre_num') . $use_order_id);
    }

    if ($action == 'info') {
        fn_sw_telegram_coustruct_tg_message($message, __('order') . ' №' . $use_order_id);
    }


    $order_status = !empty($order['order_status']) ? $order['order_status'] : $order['status'];
    $order_status_text = fn_sw_telegram_get_order_info_status($order_id, $order_status);

    fn_sw_telegram_coustruct_tg_message($message, __('order_status') . ': <b>' . $order_status_text  . '</b>');

    fn_sw_telegram_coustruct_tg_message($message, __('tg_order_summ') . fn_format_price($order['subtotal']) . ' ' . strip_tags($currency_settings['symbol']));

    fn_sw_telegram_coustruct_tg_message($message, __('shipping_cost') . ': ' . fn_format_price($order['shipping_cost']) . ' ' . strip_tags($currency_settings['symbol']));

    fn_sw_telegram_coustruct_tg_message($message, __('order_discount') . ': ' . fn_format_price($order['subtotal_discount']) . ' ' . strip_tags($currency_settings['symbol']));

    fn_sw_telegram_coustruct_tg_message($message, __('total') . ': ' . fn_format_price($order['total']) . ' ' . strip_tags($currency_settings['symbol']));


    if (fn_allowed_for('MULTIVENDOR')) {
        fn_sw_telegram_coustruct_tg_message($message, __('vendor') . ': ' . fn_get_company_name($order['company_id']));
    }

    fn_sw_telegram_coustruct_tg_message($message, '');


    $first_name_filed = '';
    if (isset($order['firstname']) && !empty($order['firstname'])) {
        $first_name_filed = $order['firstname'] . ' ' . $order['lastname'];
    }

    if (empty($first_name_filed)) {
        $first_name_filed = isset($_REQUEST['user_data']['first_name_and_last_name']) ? $_REQUEST['user_data']['first_name_and_last_name'] : '';
    }

    if (
        empty($first_name_filed)
        && (!empty($order['s_firstname']) || !empty($order['b_firstname']))
    ) {
        $first_name_filed = !empty($order['s_firstname']) ? !empty($order['s_firstname']) : $order['b_firstname'];
    }

    if (!empty($first_name_filed)) {
        fn_sw_telegram_coustruct_tg_message($message, __('first_name') . ': ' . $first_name_filed);
    }

    if (isset($order['s_city']) || isset($order['b_city'])) {
        $city = isset($order['s_city']) ?  $order['s_city'] : '';
        if (empty($city)) {
            $city = isset($order['b_city']) ?  $order['b_city'] : '';
        }
        if (!empty($city)) {
            fn_sw_telegram_coustruct_tg_message($message, __('city') . ': ' . $city);
        }
    }

    if (isset($order['s_address']) || isset($order['b_address'])) {
        $address = isset($order['s_address']) ?  $order['s_address'] : '';
        if (empty($address)) {
            $address = isset($order['b_address']) ?  $order['b_address'] : '';
        }
        if (!empty($address)) {
            fn_sw_telegram_coustruct_tg_message($message, __('address') . ': ' . $address);
            fn_sw_telegram_coustruct_tg_message($message, '');
        }
    }

    if (isset($order['s_address_2']) || isset($order['b_address_2'])) {
        $address = isset($order['s_address_2']) ?  $order['s_address_2'] : '';
        if (empty($address)) {
            $address = isset($order['b_address_2']) ?  $order['b_address_2'] : '';
        }
        if (!empty($address)) {
            fn_sw_telegram_coustruct_tg_message($message, __('address_2') . ': ' . $address);
        }
    }

    if (isset($order['s_zipcode']) || isset($order['b_zipcode'])) {
        $zipcode = isset($order['s_zipcode']) ?  $order['s_zipcode'] : '';
        if (empty($zipcode)) {
            $zipcode = isset($order['b_zipcode']) ?  $order['b_zipcode'] : '';
        }
        if (!empty($zipcode)) {
            fn_sw_telegram_coustruct_tg_message($message, __('sw_telegram.zipcode') . ': ' . $zipcode);
        }
    }

    fn_sw_telegram_coustruct_tg_message($message, '');

    $phone = '';

    if (isset($order['s_phone']) || isset($order['b_phone'])) {
        $phone = isset($order['s_phone']) ?  $order['s_phone'] : '';
        if (empty($phone)) {
            $phone = isset($order['b_phone']) ?  $order['b_phone'] : '';
        }
    }
    if (empty($phone) && isset($order['phone'])) {
        $phone = $order['phone'];
    }
    if (!empty($phone)) {
        fn_sw_telegram_coustruct_tg_message($message, __('phone') . ': ' . $phone);
    }


    if (!empty($order['email'])) {
        fn_sw_telegram_coustruct_tg_message($message, __('email') . ': ' . $order['email']);
    }

    $payment = '-';
    if (isset($order['payment_method']['payment'])) {
        $payment = $order['payment_method']['payment'];
    }

    fn_sw_telegram_coustruct_tg_message($message, __('payment_method') . ': ' . $payment);

    $shipping_ids = $order['shipping_ids'];
    if (!empty($shipping_ids)) {

        $shipping = array_shift($order['shipping']);

        fn_sw_telegram_coustruct_tg_message($message, __('shipping_method') . ': ' . $shipping['shipping']);

        if (isset($shipping['store_location_id']) && !empty($shipping['store_location_id'])) {
            $stores = isset($shipping['data']['stores'][$shipping['store_location_id']]) ? $shipping['data']['stores'][$shipping['store_location_id']] : '';

            if (!empty($stores)) {
                fn_sw_telegram_coustruct_tg_message($message,  $stores['name']);

                if (isset($stores['pickup_address']) && !empty($stores['pickup_address'])) {
                    fn_sw_telegram_coustruct_tg_message($message,  strip_tags($stores['pickup_address']));
                }

                if (isset($stores['pickup_phone']) && !empty($stores['pickup_phone'])) {
                    fn_sw_telegram_coustruct_tg_message($message,  strip_tags($stores['pickup_phone']));
                }

                if (isset($stores['pickup_time']) && !empty($stores['pickup_time'])) {
                    fn_sw_telegram_coustruct_tg_message($message,  strip_tags($stores['pickup_time']));
                }

                if (isset($stores['description']) && !empty($stores['description'])) {
                    fn_sw_telegram_coustruct_tg_message($message,  strip_tags($stores['description']));
                }
            }
        }
    }

    fn_sw_telegram_coustruct_tg_message($message, '');
    fn_sw_telegram_coustruct_tg_message($message, __('tg_products_in_order'));

    $products = $order['products'];

    foreach ($products as $product) {
        fn_sw_telegram_coustruct_tg_message($message, $product['product']);

        if (!empty($product['product_code'])) {
            fn_sw_telegram_coustruct_tg_message($message, __('sku') . ': ' . $product['product_code']);
        }
        fn_sw_telegram_coustruct_tg_message($message, __('qty') . ': ' . $product['amount'] . ' x ' . fn_format_price($product['price']) . ' ' . strip_tags($currency_settings['symbol']));

        /*Options*/
        if (isset($product['extra']['product_options_value'])) {
            $product_options_value = $product['extra']['product_options_value'];
            if (!empty($product_options_value)) {

                fn_sw_telegram_coustruct_tg_message($message, '');
                fn_sw_telegram_coustruct_tg_message($message, __('options') . ':');


                foreach ($product_options_value as $option) {

                    $option_name = !empty($option['option_name']) ? $option['option_name'] :
                        $option['internal_option_name'];

                    $variant_name = $option['variant_name'];
                    if ($option['option_type'] == 'C') {
                        if ($variant_name == 'Yes') {
                            $variant_name = __('yes');
                        } else {
                            $variant_name = __('no');
                        }
                    } elseif ($option['option_type'] == 'F') {
                        if (isset($option['value']) && !empty($option['value'])) {
                            $variant_name = __('yes');
                        } else {
                            $variant_name = __('no');
                        }
                    }

                    $opt_str = $option_name . ': ' . $variant_name;

                    fn_sw_telegram_coustruct_tg_message($message, $opt_str);
                }
            }
        }

        fn_sw_telegram_coustruct_tg_message($message, '');
    }

    if (isset($order['notes']) && !empty($order['notes'])) {
        fn_sw_telegram_coustruct_tg_message($message, '');
        fn_sw_telegram_coustruct_tg_message($message,  __('customer_notes') . ': ' . $order['notes']);
    }

    return $message;
}

/** Get order status
 * @param int $order_id
 * @param char $status
 * @return string $status text
 */
function fn_sw_telegram_get_order_info_status($order_id, $status = '')
{

    $order_info = db_get_row("SELECT order_id, company_id, lang_code, status FROM ?:orders WHERE order_id = ?i", $order_id);

    $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true, false, ($order_info['lang_code'] ? $order_info['lang_code'] : CART_LANGUAGE), $order_info['company_id']);

    if (!empty($status)) {
        $status = $order_statuses[$status]['description'];
    } else {
        $status = $order_statuses[$order_info['status']]['description'];
    }

    return $status;
}

/** Coustruct message function
 * @param string Message
 * @param string $var - text data add in message
 */
function fn_sw_telegram_coustruct_tg_message(&$message, $var)
{
    $message = $message . $var . "\r\n";
}

/** Get chat id user
 * @param array order info
 * @return string $chat_id
 */
function fn_sw_telegram_get_chat_id_user($order_info)
{
    if ($order_info['user_id'] > 0) {
        list($tg_info) = fn_sw_telegram_check_chat_id($order_info['user_id'], true);

        if ($tg_info['noty_tg'] == 'Y') {
            $chat_id = $tg_info['chat_id'];

            if (empty($chat_id)) {
                $chat_id = $order_info['chat_id'];
            }
        }
    } else {
        $chat_id = isset($order_info['chat_id']) ? $order_info['chat_id'] : '';
    }

    return $chat_id;
}

/** Tarif settings for vendors
 * @param int $company_id
 * @return bool $status
 */
function fn_sw_telegram_allow_for_tarif($company_id)
{
    $status = false;

    if (Registry::get('addons.sw_telegram.tg_allow_in_tarifs') == 'N') {
        return true;
    }

    $plan_id = db_get_field('SELECT plan_id FROM ?:companies WHERE company_id = ?i', $company_id);

    if (!empty($plan_id)) {
        $noty_tg = db_get_field('SELECT noty_tg FROM ?:vendor_plans WHERE plan_id = ?i', $plan_id);

        if ($noty_tg == 'Y') {
            $status = true;
        }
    }

    return $status;
}

/** Set webhook
 * @return string $url
 */
function fn_sw_telegram_setWebhook()
{
    $botToken = Registry::get('addons.sw_telegram.sw_bot_token');
    $url = fn_url('system_tg.webhook?token=' . $botToken, 'C');
    $url_red = 'https://api.telegram.org/bot' . $botToken . '/setWebhook?url=' . $url;
    return $url_red;
}


/** Processed_callback_query
 * @param array $data
 */
function fn_sw_telegram_processed_callback_query($data)
{

    if (!isset($data['callback_query']['data']) || empty($data['callback_query']['data'])) {
        return;
    }

    $callback_query = $data['callback_query'];
    $chat_id = $callback_query['from']['id'];

    $next = fn_sw_telegram_get_base_command($callback_query['data'], $chat_id);

    if ($next == false) {
        return;
    }

    $callback_data = fn_explode(TG_DELIM, $callback_query['data']);
    $command = $callback_data[0];

    /*Command Change order status*/
    if ($command == TG_ORDER_STATUS) {
        $order_id = $callback_data[1];
        $status = $callback_data[2];

        $force_notification['T'] = true;
        fn_change_order_status($order_id, $status, '', $force_notification);

        fn_sw_telegram_send_tg('sendMessage', $chat_id, __('status_changed'));
    }

    /*Command order info*/
    if ($command == TG_ORDER_INFO) {
        $order_id = $callback_data[1];
        $order = fn_get_order_info($order_id);
        $message = fn_sw_telegram_get_order_message($message, $order, $auth);

        fn_sw_telegram_send_tg('sendMessage', $chat_id, $message);
    }



    return;
}

/** Webhook processed
 * Get message text
 * @param array $sata
 */
function fn_sw_telegram_processed_telegram_hook($data)
{

    if (isset($data['callback_query'])) {
        fn_sw_telegram_processed_callback_query($data);
        return;
    }

    if (empty($data['message']['from']['id'])) {
        return;
    }

    $chat_id = $data['message']['from']['id'];

    $text = $data['message']['text'];
    $text_lower = strtolower($text);

    if ($text == '/test') {
        /*TEST MESSAGE*/
        /*fn_sw_telegram_send_tg('sendMessage', $chat_id, $f);*/
    } elseif (preg_match('/^ord/', $text_lower)  == true || preg_match('/^заказ/', $text)  == true || preg_match('/^Заказ/', $text)  == true) {

        $order_id = 0;

        $check_order = fn_sw_telegram_check_allow_change_order($chat_id);
        if ($check_order == false) {
            fn_sw_telegram_send_tg('sendMessage', $chat_id, __('sw_telegram.keyboard_no_found') . '. ' . __('sw_telegram.use') . ' /start');
            return;
        }

        $text_explode = fn_explode(' ', $text);
        $order_id = (int) $text_explode[1];

        $addon_settings = Registry::get('addons.sw_telegram');
        $order_id_fake = false;

        /*For AB addon ab__hide_order_id*/
        if ($addon_settings['ab__hoi_mask_id'] == 'Y' && Registry::get('addons.ab__hide_order_id.status') == 'A') {
            $order_id_fake = $text_explode[1];
            $order_id_real = fn_ab__hoi_get_order_id_by_mask_id($order_id_fake);
            $order_info = fn_get_order_info($order_id_real);

            $order_id = $order_id_real;
        } else {
            $order_info = fn_get_order_info($order_id);
        }

        if (empty($order_info)) {
            fn_sw_telegram_send_tg('sendMessage', $chat_id, __('sw_telegram.order_not_found'));
            return;
        }

        $lang_code = $order_info['lang_code'];
        $company_id = $order_info['company_id'];


        $check_order = fn_sw_telegram_check_allow_change_order($chat_id, $company_id);

        if ($check_order == false) {
            fn_sw_telegram_send_tg('sendMessage', $chat_id, __('sw_telegram.order_not_found'));
            return;
        }

        $statuses = fn_get_statuses(STATUSES_ORDER, [], false, false, $lang_code, $company_id);
        $statuses_btn = [];
        foreach ($statuses as $status_data) {
            $statuses_btn[] = [
                'text' => $status_data['description'],
                'callback_data' => TG_ORDER_STATUS . TG_DELIM . $order_id . TG_DELIM . $status_data['status']
            ];
        }

        $statuses_btn[] = [
            'text' => __('order_info'),
            'callback_data' => TG_ORDER_INFO . TG_DELIM . $order_id . TG_DELIM
        ];

        $statuses_btn = array_chunk($statuses_btn, 2);

        $buttons = array(
            'inline_keyboard' => $statuses_btn,
            'resize_keyboard' => false,
            'one_time_keyboard' => true,
        );
        $encodedMarkup = json_encode($buttons);

        if ($order_id_fake != false) {
            $order_id = $order_id_fake;
        }

        $content = array(
            'chat_id' => $chat_id,
            'reply_markup' => $encodedMarkup,
            'text' => __('sw_telegram.how_status', ["[order_id]" => $order_id])
        );

        fn_sw_telegram_send_tg_buttons($content);
    } elseif ($text == '/start' || $text == '/') {

        /*$mess  = str_replace(array("<br />", "<br/>", "<br>"), "", __('sw_telegram.noty_user_information_text'));

        fn_sw_telegram_send_tg('sendMessage', $chat_id, $mess);

        $change_allow = fn_sw_telegram_check_allow_change_order($chat_id);
        if ($change_allow == true) {
            fn_sw_telegram_send_tg('sendMessage', $chat_id, __('sw_telegram.command_change_status'));
        }
        */

        $statuses_btn = [
            [
                'text' => __('sw_telegram.comm_id'),
                'callback_data' => 'id'
            ],
            [
                'text' => __('sw_telegram.comm_subscribe'),
                'callback_data' => 'subscribe'
            ],

            [
                'text' => __('sw_telegram.comm_last_order'),
                'callback_data' => 'last_order'
            ],

            [
                'text' => __('sw_telegram.comm_stop'),
                'callback_data' => 'stop'
            ],

        ];

        $change_allow = fn_sw_telegram_check_allow_change_order($chat_id);
        if ($change_allow == true) {
            $statuses_btn[] = [
                'text' => __('sw_telegram.comm_change_status'),
                'callback_data' => 'change_status'
            ];
        }

        $statuses_btn = array_chunk($statuses_btn, 2);

        $buttons = array(
            'inline_keyboard' => $statuses_btn,
            'resize_keyboard' => false,
            'one_time_keyboard' => true,
        );
        $encodedMarkup = json_encode($buttons);

        if ($order_id_fake != false) {
            $order_id = $order_id_fake;
        }

        $content = array(
            'chat_id' => $chat_id,
            'reply_markup' => $encodedMarkup,
            'text' => __('sw_telegram.comm_choose')
        );

        fn_sw_telegram_send_tg_buttons($content);
    } elseif ($text == __('sw_telegram.noty_user_info')) {
        $params_stick = array(
            'send_stiker' => true,
            'sticker' => HELLO_STICKER,
            'parse_mode' => 'json'
        );
        fn_sw_telegram_send_tg('sendSticker', $chat_id, '', $params_stick);

        $message = __('sw_telegram.noty_user_info_yet');
        fn_sw_telegram_send_tg('sendMessage', $chat_id, $message);
    } elseif ($text == __('sw_telegram.keyboard_unsubscr') || $text == 'STOP' || $text == '/stop' || $text == '/unsubscribe') {
        fn_sw_telegram_get_base_command_subscribe($chat_id);
    } elseif ($text == __('sw_telegram.keyboard_subscr') || $text == '/subscribe') {
        fn_sw_telegram_get_base_command_subscribe($chat_id, 'Y');
    } elseif ($text == __('sw_telegram.keyboard_last_status') || $text == '/last_order') {
        fn_sw_telegram_get_base_command_get_last_order($chat_id);
    } elseif ($var = fn_sw_telegram_check_tg_command('/start', $text)) {

        $var_text = base64_decode($var);
        $user_id = $order_id = 0;

        if (strpos($var_text, 'user') !== false) {
            $_user_data = fn_explode('_', $var_text);
            $user_id = $_user_data[1];
        } elseif (strpos($var_text, 'order') !== false) {
            $_order_data = fn_explode('_', $var_text);
            $order_id = $_order_data[1];
            $user_id = db_get_field('SELECT user_id FROM ?:orders WHERE order_id = ?i', $order_id);
        }
        if ($user_id != false) {

            /* Set more chats - no used
            $get_user_chats = db_get_field('SELECT chat_id FROM ?:users WHERE user_id = ?i', $user_id);
            $chat_set = $chat_id;
            $is_set = false;
            $delim = '';
            
            if (!empty($get_user_chats)) {
                $delim = ',';
                $get_user_chats_ex = fn_explode(',', $get_user_chats);
                foreach ($get_user_chats_ex as $get_user_chats_ex_id) {
                    if (trim($get_user_chats_ex_id) == $chat_id) {
                        $is_set = true;
                    }
                }
            }
            
            if ($is_set == false) {
                $chat_set = $get_user_chats . $delim . $chat_id;
            }
            */

            db_query('UPDATE ?:users SET chat_id = ?i, noty_tg = ?s, chat_id_timestamp = ?i WHERE user_id = ?i', $chat_id, "Y", TIME, $user_id);

            fn_sw_telegram_send_tg('sendMessage', $chat_id, __('sw_telegram.noty_user_ok_sbscr'));
        } elseif ($order_id != false) {
            db_query('UPDATE ?:orders SET chat_id = ?i WHERE order_id = ?i', $chat_id, $order_id);
            fn_sw_telegram_send_tg('sendMessage', $chat_id, __('sw_telegram.noty_user_subscr_order') . $order_id);
        }
    } elseif ($text == '/id') {

        fn_sw_telegram_send_tg('sendMessage', $chat_id, $chat_id);
    } else {
        fn_sw_telegram_send_tg('sendMessage', $chat_id, __('sw_telegram.keyboard_no_found') . '. ' . __('sw_telegram.use') . ' /start');
    }

    return;
}

/** Check command
 * @param string $command
 * @param string $text
 * @return bool true|false
 */
function fn_sw_telegram_check_tg_command($command, $text)
{
    if (strpos($text, $command) !== false) {
        return trim(str_replace($command, '', $text));
    }

    return false;
}

/** Check chat_id
 * @param int $user_id
 * @param bool true|false $noty_tg
 * @return int $chat_id
 */
function fn_sw_telegram_check_chat_id($user_id, $noty_tg = false)
{
    if ($noty_tg == true) {
        $noty_tg_info = db_get_array('SELECT chat_id, noty_tg FROM ?:users WHERE user_id = ?i', $user_id);
        return $noty_tg_info;
    }

    $chat_id = db_get_field('SELECT chat_id FROM ?:users WHERE user_id = ?i', $user_id);

    return $chat_id;
}

/** Check noty user
 * @param int $user_id
 * @return bool true|false
 */
function fn_sw_telegram_check_noty_user($user_id)
{
    $noty_tg_info = db_get_array('SELECT chat_id, noty_tg FROM ?:users WHERE user_id = ?i', $user_id);

    if ($noty_tg_info['noty_tg'] == 'Y' && $noty_tg_info['chat_id'] != false) {
        return true;
    }

    return false;
}

/** Get status order
 * @param array $order
 * @return char $status
 */
function fn_sw_telegram_get_status_order($order)
{
    $status_order = '';

    if (!isset($order['status'])) {
        return $status_order;
    }

    $status_order_list = fn_get_statuses();
    if (!empty($status_order_list)) {
        foreach ($status_order_list as $status_order_elm) {
            if ($order['status'] == $status_order_elm['status']) {
                $status_order = $status_order_elm['description'];
                break;
            }
        }
    }
    return $status_order;
}

/* Get vendors chats
 * @param (int) $company_id
 * @return (array) $chat_list
*/
function fn_sw_telegram_get_vendor_chats($company_id)
{
    $chat_ids = array();
    $params = array(
        'company_id' => $company_id,
        'status' => 'A',
        'user_type' => 'V',
    );

    if (fn_sw_telegram_allow_for_tarif($company_id) == true) {
        list($users_vendor,) = fn_get_users($params, $_SESSION['auth']);
    }

    if (empty($users_vendor)) {
        return '';
    }

    $chat_list = [];
    foreach ($users_vendor as $vendor) {

        if ($vendor['noty_tg'] == "N") {
            continue;
        }

        $chat_id_vendors = fn_explode(',', $vendor['chat_id']);
        if (empty($chat_id_vendors)) {
            continue;
        }
        $chat_list += $chat_id_vendors;
    }

    return $chat_list;
}


/* Check allow change order by user chat
@param (int) $chat_id
@param (int) $company_id
@return (bool) true|false
*/
function fn_sw_telegram_check_allow_change_order($chat_id, $company_id = '')
{
    $user_list = db_get_array("SELECT user_id, chat_id FROM ?:users WHERE chat_id LIKE (?l) AND user_type = ?s", '%' . $chat_id . '%', 'A');
    $vendor_list = [];

    if (fn_allowed_for('MULTIVENDOR') && Registry::get('addons.sw_telegram.tg_allow_change_order_status') == 'Y') {
        $condition = '';
        if (!empty($company_id)) {
            $condition = db_quote(" AND company_id = ?i", $company_id);
        }

        $vendor_list = db_get_array("SELECT user_id, chat_id FROM ?:users WHERE chat_id LIKE (?l) AND user_type = ?s $condition",  '%' . $chat_id . '%', 'V');
    }

    $user_list += $vendor_list;

    $allow = false;

    if (empty($user_list)) {
        return $allow;
    }

    foreach ($user_list as $user_data) {
        $chat_ids = fn_explode(',', $user_data['chat_id']);
        foreach ($chat_ids as $id) {
            if ($chat_id == trim($id)) {
                $allow = true;
            }
        }
    }

    return $allow;
}

/*
* Get users active list
*
*/

function fn_sw_telegram__get_data_list($params = array(), $items_per_page = 0, $lang_code = CART_LANGUAGE)
{

    $db_name = '?:users';
    $db_name_descr = '?:orders';
    $id_field = 'chat_id';

    // Unset all SQL variables
    $fields = array();

    $sortings = array(
        'user_id'   => $db_name . '.user_id',
        'email'   => $db_name . '.email',
        'phone'   => $db_name . '.phone',
        'firstname'   => $db_name . '.firstname',
        'chat_id'   => $db_name . '.chat_id',
        'chat_id_timestamp'   => $db_name . '.chat_id_timestamp',
        'noty_tg'   => $db_name . '.noty_tg'
    );

    $condition = $limit = $join = '';

    $default_params = array(
        'page' => 1,
        'items_per_page' => $items_per_page,
    );

    $params = array_merge($default_params, $params);

    if (!empty($params['limit'])) {
        $limit = db_quote(' LIMIT 0, ?i', $params['limit']);
    }

    $sorting = db_sort($params, $sortings, 'user_id', 'desc');

    $fields = array(
        $db_name . '.user_id',
        $db_name . '.user_type',
        $db_name . '.chat_id',
        $db_name . '.firstname',
        $db_name . '.lastname',
        $db_name . '.email',
        $db_name . '.phone',
        $db_name . '.noty_tg',
        $db_name . '.chat_id_timestamp',
        //  $db_name_descr.'.order_id',
        //   $db_name_descr.'.chat_id as order_chat_id',
        //   $db_name_descr.'.firstname as order_firstname',
        //   $db_name_descr.'.lastname as order_lastname',
        //   $db_name_descr.'.email as order_email'
    );

    $condition .= db_quote(" and ($db_name.chat_id > ?i)", 0);

    if (!empty($params['email'])) {
        $params['email'] = trim($params['email']);
        $condition .= db_quote(" and ($db_name.email LIKE ?l)",  '%' . $params['email'] . '%');
    }

    if (!empty($params['phone'])) {
        $params['phone'] = trim($params['phone']);
        $condition .= db_quote(" and ($db_name.phone LIKE ?l)",  '%' . $params['phone'] . '%');
    }

    if (!empty($params['user_type'])) {
        $condition .= db_quote(" and $db_name.user_type = ?s",  $params['user_type']);
    }

    if (!empty($params['chat_id'])) {
        $params['chat_id'] = trim($params['chat_id']);
        $condition .= db_quote(" and ($db_name.chat_id = ?s)",  $params['chat_id']);
    }

    if (!empty($params['noty_tg'])) {
        $condition .= db_quote(" and ($db_name.noty_tg = ?s)",  $params['noty_tg']);
    }

    //join
    // $join .= db_quote( " LEFT JOIN $db_name_descr ON $db_name_descr.chat_id = $db_name.chat_id " );

    //paginate count
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM $db_name $join WHERE 1 $condition");
        $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
    }

    $data = db_get_hash_array(
        "SELECT ?p FROM $db_name " .
            $join .
            'WHERE 1 ?p ?p ?p',
        $id_field,
        implode(', ', $fields),
        $condition,
        $sorting,
        $limit
    );

    $d = db_quote(
        "SELECT ?p FROM $db_name " .
            $join .
            'WHERE 1 ?p ?p ?p',
        implode(', ', $fields),
        $condition,
        $sorting,
        $limit
    );

    return [$data, $params];
}

function fn_sw_telegram_get_base_command($command, $chat_id)
{

    $next = true;

    switch ($command) {
        case 'id':
            fn_sw_telegram_send_tg('sendMessage', $chat_id, $chat_id);
            $next = false;
            break;
        case 'subscribe':
            fn_sw_telegram_get_base_command_subscribe($chat_id, 'Y');
            $next = false;
            break;
        case 'last_order':
            fn_sw_telegram_get_base_command_get_last_order($chat_id);
            $next = false;
            break;
        case 'stop':
            fn_sw_telegram_get_base_command_subscribe($chat_id);
            $next = false;
            break;
        case 'change_status':
            fn_sw_telegram_send_tg('sendMessage', $chat_id, __('sw_telegram.command_change_status'));
            $next = false;
            break;
    }

    return $next;
}

function fn_sw_telegram_get_base_command_subscribe($chat_id, $status = 'N')
{

    $user_id = db_get_field('SELECT chat_id FROM ?:users WHERE chat_id = ?i', $chat_id);
    $res = db_query('UPDATE ?:users SET noty_tg = ?s, chat_id_timestamp = ?i WHERE chat_id = ?i', $status, TIME, $chat_id);

    if ($status == 'N') {
        if ($user_id) {
            fn_sw_telegram_send_tg('sendMessage', $chat_id, __('sw_telegram.noty_user_unsubscr'));
        } else {
            $message = __('sw_telegram.noty_user_no_account');
            $message .= "\r\n" . __('sw_telegram.can_register') . ' ' . Registry::get('config.current_host');
            fn_sw_telegram_send_tg('sendMessage', $chat_id, $message);
        }
    } else {
        if ($user_id) {
            fn_sw_telegram_send_tg('sendMessage', $chat_id, __('sw_telegram.noty_user_info_yet'));
        } else {

            $message = __('sw_telegram.noty_user_no_account');
            $message .= "\r\n" . __('sw_telegram.can_register') . ' ' . Registry::get('config.current_host');

            fn_sw_telegram_send_tg('sendMessage', $chat_id, $message);
        }
    }

    return;
}

function fn_sw_telegram_get_base_command_get_last_order($chat_id)
{
    $user_data = db_get_row('SELECT user_id, lang_code FROM ?:users WHERE chat_id = ?i', $chat_id);

    if (!empty($user_data)) {

        $user_id = $user_data['user_id'];
        $lang_code = $user_data['lang_code'];

        $order_id = db_get_field('SELECT MAX(order_id) FROM ?:orders WHERE user_id = ?i', $user_id);
    } else {
        $order_id = db_get_field('SELECT MAX(order_id) FROM ?:orders WHERE chat_id = ?i', $chat_id);
    }

    if (!empty($order_id)) {
        $order = fn_get_order_info($order_id);
        $message = fn_sw_telegram_get_order_message($message = '', $order, array(), 'info');
    } else {
        $message = __('sw_telegram.order_no_found');
        $message .= "\r\n" . __('sw_telegram.can_register') . ' ' . Registry::get('config.current_host');
    }

    fn_sw_telegram_send_tg('sendMessage', $chat_id, $message);
    return;
}
