<?php

use Tygh\Registry;
use Tygh\Settings;
use Tygh\Http;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

if ($mode == 'get_id_chat') {

    $chat_id = fn_sw_telegram_get_chat_id();
    $message = __('sw_telegrame_your_chat_id') . " " . $chat_id;
    fn_sw_telegram_send_tg('sendMessage', $chat_id, $message);

    return array(CONTROLLER_STATUS_OK, fn_url('addons.update&addon=sw_telegram'));
} elseif ($mode == 'info') {

    $owner_of_product = fn_get_company_by_product_id(214);
    fn_print_die($owner_of_product);

    $botToken = Registry::get('addons.sw_telegram.sw_bot_token');
    $website = "https://api.telegram.org/bot" . $botToken;
    $get_chat_id = $website . "/getUpdates";

    $data = array();
    $array_result = Http::get($get_chat_id, $data);

    exit;
} elseif ($mode == 'set_user') {

    $bot_name = Registry::get('addons.sw_telegram.sw_bot_name');
    $encode = '';

    if (!empty($res['order_id'])) {
        $encode = base64_encode($res['order_id']);
        $encode = str_replace(array("="), "", $encode);
    }

    if (!empty($res['user'])) {
        $encode = base64_encode($res['user']);
        $encode = str_replace(array("="), "", $encode);
    }
    if (!empty($encode) && !empty($bot_name)) {
        fn_redirect(fn_url('https://t.me/' . $bot_name . '?start=' . $encode), true, true);
    }

    die();
}