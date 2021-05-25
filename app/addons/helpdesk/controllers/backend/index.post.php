<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'index') {
    $general_stats = Tygh::$app['view']->getTemplateVars('general_stats');

    list($tickets, $search) = fn_get_tickets(['status' => 'N']);

    $general_stats['new_tickets'] = $search['total_items'];

    Tygh::$app['view']->assign('general_stats', $general_stats);
}
