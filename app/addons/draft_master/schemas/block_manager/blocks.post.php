<?php

$schema['extra_user_data'] = array (
    'content' => array (
        'extra_user_data' => array (
            'type' => 'function',
            'function' => array('fn_get_extra_user_data'),
        ),
    ),
    'templates' => array (
        'addons/draft_master/blocks/debt_limit.tpl' => array(),
        'addons/draft_master/blocks/my_equipment.tpl' => array(),
        'addons/draft_master/blocks/contacts.tpl' => array(),
    ),
    'wrappers' => 'blocks/wrappers',
    'cache' => array(
        'update_handlers' => array(
            'user_data'
        ),
        'auth_handlers' => array(
            'user_id'
        )
    ),
);

return $schema;
