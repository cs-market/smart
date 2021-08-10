<?php

use Tygh\Registry;

if (fn_smart_distribution_is_manager(Tygh::$app['session']['auth']['user_id'])) {
    $menu = ['management' => [
        'title' => 'Management',
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
    //$menu = ['items' => '123'];
    unset($schema['top']);
    $schema['central'] = $menu;
    //fn_print_die($schema);
}

return $schema;
