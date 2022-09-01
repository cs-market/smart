<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == "manage" && $_REQUEST['object_type'] == 'O') {
    $posts = Tygh::$app['view']->getTemplateVars('posts');
    foreach ($posts as &$post) {
        $user = fn_get_user_info($post['user_id']);
        $post['name'] .= '  |  ' . $user['b_address'] . '  |  ' . $user['fields'][38];
    }

    Tygh::$app['view']->assign('posts', $posts);
}
