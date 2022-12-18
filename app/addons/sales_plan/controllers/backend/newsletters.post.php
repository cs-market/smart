<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'add') {
    $params = $_REQUEST;
    if ($params['user_ids']) {
        $newsletter_data = array(
            'users' => $params['user_ids'],
        );
        Tygh::$app['view']->assign('newsletter', $newsletter_data);
    }
}
