<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

$res = $_REQUEST;

if ($mode == 'webhook') {

    if (isset($res['set']) && $res['set'] == 1) {
        $url = fn_sw_telegram_setWebhook();
        header('Location: ' . $url);
        exit;
    }

    $data = file_get_contents('php://input');

    fn_sw_telegram_processed_telegram_hook(json_decode($data, true));

    fn_echo('ok');
    die();
}

if ($mode == 'set_user') {

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
        $bot_name = str_replace("@", '', $bot_name);
        fn_redirect(fn_url('https://t.me/' . $bot_name . '?start=' . $encode), true, true);
    }

    die();
}
