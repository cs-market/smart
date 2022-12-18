<?php

$schema['export_fields']['Storage name'] = array (
    'db_field' => 'storage',
    'table' => 'storages',
    'linked' => true,
    'export_only' => true,
);
$schema['export_fields']['Storage code'] = array (
    'db_field' => 'code',
    'table' => 'storages',
    'linked' => true,
    'export_only' => true,
);
$schema['references']['storages'] = [
    'reference_fields' => ['storage_id' => '#orders.storage_id'],
    'join_type'        => 'LEFT',
];

return $schema;
