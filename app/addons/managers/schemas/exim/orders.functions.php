<?php

function fn_exim_get_managers_names($user_id) {
    $managers = [];
    if ($managers = fn_get_managers(['user_managers' => $user_id])) {
        $managers = array_map(function($m) {
            return trim($m['firstname'] . ' ' . $m['lastname']);
        }, $managers);
    }

    return !empty($managers) ? implode(', ', $managers) : '';
}