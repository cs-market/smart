<?php

use Tygh\Registry;

if (fn_smart_distribution_is_manager(Tygh::$app['session']['auth']['user_id'])) {
    $menu = ['management' => [
        'title' => __('management'),
        'items' => [
            'view_orders' => $schema['central']['orders']['items']['view_orders'],
            'products' => $schema['central']['products']['items']['products'],
            'customers' => $schema['central']['customers']['items']['customers'],
            'customers' => $schema['central']['customers']['items']['customers'],
            'tickets' => $schema['central']['helpdesk']['items']['tickets'],
            'new_tickets' => $schema['central']['helpdesk']['items']['new_tickets'],
        ],
        'position' => 100
    ]];

    unset($schema['top']);
    $schema['central'] = $menu;
}

return $schema;
