<?php

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'draft_master/schemas/exim/users.functions.php');

$schema['export_fields']['Extra data'] = [
    'db_field' => 'extra_data',
    'only_import' => 'true',
    'linked' => false,
    'process_put' => ['fn_import_user_extra_data', '#key', '#this'],
];

return $schema;
