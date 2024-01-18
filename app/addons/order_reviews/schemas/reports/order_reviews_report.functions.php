<?php

use Tygh\Registry;

function fn_generate_order_reviews_report($params) {
    $default_params = array(
        'delimiter' => 'S',
        'period' => 'custom',
        'time_from' => strtotime("-3 month"),
        'time_to' => TIME,
        'filename' => 'order_reviews_report_' . date('dMY_His', TIME) . '.csv',
        // 'hide_zero' => "Y"
    );

    $params = array_filter($params);
    if (isset($params['period']) && $params['period'] == 'A') unset($params['period']);

    if (is_array($params)) {
        $params = array_merge($default_params, $params);
    } else {
        $params = $default_params;
    }

    list($params['time_from'], $params['time_to']) = fn_create_periods($params);

    if (Registry::get('runtime.company_id')) {
        $params['company_id'] = Registry::get('runtime.company_id');
    }

    if (!empty($params['user_id']) && !is_array($params['user_id'])) {
        $params['user_id'] = explode(',', $params['user_id']);
    }

    $output = [];
    if (isset($params['is_search'])) {
        $d_params = $params;
        $d_params['object_type'] = 'O';
        unset($d_params['type']);
        if (!is_numeric($d_params['rating_value'])) {
            unset($d_params['rating_value']);
        }
        list($discussions, $c_params) = fn_get_discussions($d_params);

        foreach ($discussions as $discussion) {
            $user = fn_get_user_info($discussion['user_id']);
            $output[] = array(
                __('customer') => $user['firstname'],
                __('address') => $user['b_address'],
                __('code') => !empty($user['fields'][39]) ? $user['fields'][39] : $user['fields'][38],
                __('date') => fn_date_format($discussion['timestamp'], Registry::get('settings.Appearance.date_format')),
                __('order_id') => $discussion['object_id'],
                __('rating') => $discussion['rating_value'],
                __('message') => $discussion['message']
            );
        }
    }

    return array($output, $params);
}
