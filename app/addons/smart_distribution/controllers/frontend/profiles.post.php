<?php

use Tygh\Registry;
use Tygh\Tools\DateTimeHelper;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	return ;
}

if ($mode == 'update') {
	if (Registry::get('settings.General.allow_usergroup_signup') != 'Y') {
		Registry::del('navigation.tabs.usergroups');
		Tygh::$app['view']->assign('usergroups', array());
	}
} elseif ($mode == 'stats') {
    if ($auth['user_id']) {
        $stats = [];
        if (isset($_REQUEST['time_from']) && !empty($_REQUEST['time_from']) && isset($_REQUEST['time_to']) && !empty($_REQUEST['time_to'])) {
            list($timestamp_from, $timestamp_to) = fn_create_periods($_REQUEST);
            $stats['time_from'] = $timestamp_from;
            $stats['time_to'] = $timestamp_to;
        } else {
            $time_period = DateTimeHelper::getPeriod(DateTimeHelper::PERIOD_THIS_MONTH);
            $stats['time_from'] = $timestamp_from = $time_period['from']->getTimestamp();
            $stats['time_to'] = $timestamp_to = $time_period['to']->getTimestamp();
        }

        $params = [
            'user_id'       => $auth['user_id'],
            'period'        => 'C',
            'time_from'     => $timestamp_from,
            'time_to'       => $timestamp_to,
            'extra'         => [],
            'storefront_id' => $storefront_id,
            ''
        ];
        list(, , $tmp) = fn_get_orders($params, 0, true);
        $stats['current_orders'] = $tmp['gross_total'] ?? 0;

        $time_difference = $timestamp_to - $timestamp_from;

        $params = [
            'user_id'       => $auth['user_id'],
            'period'        => 'C',
            'time_from'     => $timestamp_from - $time_difference,
            'time_to'       => $timestamp_to - $time_difference,
            'extra'         => [],
            'storefront_id' => $storefront_id,
        ];
        list(, , $tmp) = fn_get_orders($params, 0, true);
        $stats['prev_orders'] = $tmp['gross_total'] ?? 0;

        // TODO may be it should be in promotion progress add-on?
        $stats['shippments'] = fn_get_user_additional_data('S', $auth['user_id']);
    }

    Tygh::$app['view']->assign('stats', $stats);
}
