<?php

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'userlog') {
    $userlog = Tygh::$app['view']->getTemplateVars('userlog');
    $increases = [];
    $userlog = fn_array_value_to_key($userlog, 'change_id');
    foreach ($userlog as $change) {
        if ($change['amount'] > 0 && $change['action'] == CHANGE_DUE_ORDER && !empty($change['reason'])) {
            if ($reason = unserialize($change['reason'])) {
                if (!empty($reason['order_id']) && !isset($increases[$reason['order_id']])) {
                    $increases[$reason['order_id']] = $change['change_id'];
                }
            }
        }
    }

    if (!empty($increases)) {
        $expirations = db_get_hash_array('SELECT * FROM ?:reward_point_details WHERE order_id IN (?a)', 'order_id', array_keys($increases));
        foreach ($expirations as $order_id => $expiration) {
            $userlog[$increases[$order_id]]['expiration'] = $expiration;
        }

        $expirations = fn_sort_array_by_key($expirations, 'ttl');
        $expirations = array_filter($expirations, function($v) {
            return $v['ttl'] > TIME;
        });
        if ($expirations) {
            $nearest_expiration = array_shift($expirations);
            Tygh::$app['view']->assign('nearest_expiration', $nearest_expiration);
        }
    }

    Tygh::$app['view']->assign('userlog', array_values($userlog));
}
