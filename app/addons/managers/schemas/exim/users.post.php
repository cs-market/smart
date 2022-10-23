<?php

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'managers/schemas/exim/users.functions.php');

$schema['export_fields']['Managers'] = [
    'process_put' => array('fn_update_user_managers', '#key', '#this'),
    'process_get' => array ('fn_exim_managers_export_managers', '#key'),
    'import_only' => false,
    'linked' => false
];

return $schema;