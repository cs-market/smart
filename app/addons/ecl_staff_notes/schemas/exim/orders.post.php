<?php

$schema['export_fields']['Customer notes'] = array(
    'db_field' => 'staff_notes',
    'table' => 'users',
);

$schema['references']['users'] = [
    'reference_fields' => [
        'user_id' => '#orders.user_id'
    ],
    'join_type' => 'LEFT'
];

return $schema;
