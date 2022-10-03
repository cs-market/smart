<?php

$schema['extra_user_data'] = array (
    'templates' => array (
        'addons/draft_master/blocks/debt_limit.tpl' => array(),
        'addons/draft_master/blocks/my_equipment.tpl' => array(),
        'addons/draft_master/blocks/contacts.tpl' => array(),
    ),
    'wrappers' => 'blocks/wrappers',
    'cache' => array(
        'update_handlers' => array(
            'user_data'
        )
    ),
);

return $schema;
