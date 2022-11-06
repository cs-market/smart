<?php
use Tygh\Enum\UserRoles;
use Tygh\Registry;

if (UserRoles::is_manager()) {
    $menu = ['management' => [
        'title' => __('management'),
        'items' => [
            'view_orders' => $schema['central']['orders']['items']['view_orders'],
            'products' => $schema['central']['products']['items']['products'],
            'customers' => $schema['central']['customers']['items']['customers'],
            'customers' => $schema['central']['customers']['items']['customers'],
        ],
        'position' => 100
    ]];

    if (Registry::get('addons.helpdesk.status') == 'A') {
        $menu['management']['items']['tickets'] = $schema['central']['helpdesk']['items']['tickets'];
        $menu['management']['items']['new_tickets'] = $schema['central']['helpdesk']['items']['new_tickets'];
    }

    $schema['top'] = [];
    $schema['central'] = $menu;
}

return $schema;
