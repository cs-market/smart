<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

include_once(Registry::get('config.dir.addons') . 'maintenance/schemas/exim/exim.functions.php');

$schema['export_fields']['Reward points'] = [
    'process_get' => array('unserialize', '#this'),
    'export_only' => true,
    'db_field' => 'data',
    'table' => 'user_data',
];

$schema['export_fields']['Add usergroup IDs'] = [
    'process_put' => array('fn_maintenance_exim_set_usergroups', '#key', '#this', false),
    'import_only' => true,
    'linked' => false,
];

if (isset($schema['export_fields']['User group IDs']['process_put'])) {
    $schema['export_fields']['User group IDs']['process_put'][0] = 'fn_maintenance_exim_set_usergroups';
}

// backward compatibility
$schema['export_fields']['Usergroup IDs'] = $schema['export_fields']['User group IDs'];

return $schema;
