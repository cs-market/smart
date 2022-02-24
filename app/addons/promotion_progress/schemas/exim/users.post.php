<?php

$schema['export_fields']['Total sales'] = [
    'process_put' => array('fn_save_user_additional_data', 'S', '#this', '#key'),
    'import_only' => true,
    'linked' => false,
];

return $schema;
