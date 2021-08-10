<?php

function fn_smart_distribution_get_managers_names($user_id) {
    $data = fn_smart_distribution_get_managers(['user_id' => $user_id]);
    return !empty($data) ? implode(', ', array_column($data, 'name')) : '';
}