<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'assign_user' && !empty($_REQUEST['user_id'])) {
        $ekey = fn_generate_ekey('telegram_auth', 'T', 60*5, null, ['object_type' => 'user', 'object_id' => $_REQUEST['user_id']]);
        fn_redirect('https://t.me/' . Registry::get('addons.telegram.bot_name') . "?start=$ekey", true);
    }
}
