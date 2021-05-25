<?php

use Tygh\Registry;

$schema['central']['helpdesk']['items']['tickets'] = array(
    'href' => 'tickets.manage?status=',
    'position' => 300,
    'attrs' => array(
        'class'=>'is-addon'
    ),
);

$schema['central']['helpdesk']['items']['new_tickets'] = array(
    'href' => 'tickets.manage?status=N',
    'alt' => 'tickets.view',
    'position' => 400,
    'attrs' => array(
        'class'=>'is-addon'
    ),
);

$schema['central']['helpdesk']['items']['waiting_tickets'] = array(
    'href' => 'tickets.manage?status=W',
    'alt' => 'tickets.view',
    'position' => 550,
    'attrs' => array(
        'class'=>'is-addon'
    ),
);

$schema['central']['helpdesk']['items']['mailboxes'] = array(
    'href' => 'mailboxes.manage',
    'position' => 700,
    'attrs' => array(
        'class'=>'is-addon'
    ),
);

$schema['central']['helpdesk']['items']['message_templates'] = array(
    'href' => 'message_templates.manage',
    'position' => 800,
    'attrs' => array(
        'class'=>'is-addon'
    ),
);

$schema['central']['helpdesk']['position'] = '700';

if (Registry::ifGet('config.tweaks.validate_menu', false)) {
    $schema['central']['marketing']['items']['helpdesk'] = array(
        'href' => 'tickets.manage',
        'position' => 10,
        'attrs' => array(
            'class'=>'is-addon'
        ),
    );
    $schema['central']['marketing']['items']['helpdesk']['subitems'] = $schema['central']['helpdesk']['items'];
    unset($schema['central']['helpdesk']);
}

return $schema;
