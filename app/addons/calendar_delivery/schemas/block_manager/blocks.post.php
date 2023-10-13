<?php

$schema['nearest_delivery'] = array (
    'content' => array (
        'shipping' => array (
            'type' => 'function',
            'function' => array('fn_calendar_delivery_get_shipping_params'),
        ),
    ),
    'templates' => array (
        'addons/calendar_delivery/blocks/nearest_delivery.tpl' => array(),
    ),
    'wrappers' => 'blocks/wrappers',
    'cache' => false,
);

return $schema;
