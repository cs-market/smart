<?php

use Tygh\Registry;
use Tygh\Enum\SiteArea;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = (SiteArea::isStorefront(AREA)) ? $auth['user_id'] : $_REQUEST['user_id'];

    if ($mode == 'assign_user' && !empty($user_id)) {
        $ekey = fn_generate_ekey('telegram_auth', 'T', 60*5, null, ['object_type' => 'user', 'object_id' => $user_id]);
        fn_redirect('https://t.me/' . Registry::get('addons.telegram.bot_name') . "?start=$ekey", true);
    }

    if ($mode == 'unsubscribe' && !empty($user_id)) {
        db_query('UPDATE ?:users SET chat_id = ?s WHERE user_id = ?s', '', $user_id);
        fn_set_notification('N', __('notice'), __('successful'));
    }
}
