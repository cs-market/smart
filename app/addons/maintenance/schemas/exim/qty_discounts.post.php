<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

include_once(Registry::get('config.dir.addons') . 'maintenance/schemas/exim/exim.functions.php');

$schema['export_fields']['User group']['convert_put'][0] = 'fn_maintenance_exim_put_usergroup';

unset($schema['export_fields']['User group']['required']);
$schema['export_fields']['Usergroup IDs'] = $schema['export_fields']['User group'];

$schema['import_process_data'] = array(
    'check_usergroup' => array(
        'function' => 'fn_exim_check_usergroup',
        'args' => array('$object', '$processed_data', '$skip_record'),
        'import_only' => true,
    ),
);

foreach ($schema['export_fields'] as &$field) {
    if (isset($field['convert_put']) && $field['convert_put'][0] == 'fn_exim_import_price') {
        $field['convert_put'][0] = 'fn_maintenance_exim_import_price';
    }
}
unset($field);

return $schema;
