<?php

function fn_exim_managers_export_managers($user_id) {
    $managers = [];
    if ($managers = fn_get_managers(['user_managers' => $user_id])) {
        $managers = array_column($managers, 'email');
    }

    return implode(',', $managers);
}
