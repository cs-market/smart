<?php

$schema['progress_promotions'] = array (
    'content' => array (
        'progress_promotions' => array (
            'remove_indent' => true,
            'hide_label' => true,
            'type' => 'function',
            'function' => ['fn_get_progress_promotions']
        ),
    ),
    'templates' => 'addons/promotion_progress/blocks/promotions.tpl',
    'wrappers' => 'blocks/wrappers',
    'cache' => array (
        'update_handlers' => array ('promotions', 'orders'),
    )
);

return $schema;
