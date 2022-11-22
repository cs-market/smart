<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

include_once(Registry::get('config.dir.addons') . 'maintenance/schemas/exim/exim.functions.php');

foreach ($schema['export_fields'] as &$field) {
    if (isset($field['convert_put']) && $field['convert_put'][0] == 'fn_exim_import_price') {
        $field['convert_put'][0] = 'fn_maintenance_exim_import_price';
    }
}
unset($field);

foreach (['Min quantity', 'Max quantity', 'Quantity step', 'Quantity', 'Weight'] as $field) {
    if (isset($schema['export_fields'][$field])) {
        $schema['export_fields'][$field]['convert_put'] = ['fn_maintenance_exim_import_price', '#this'];
    }
}

$schema['export_fields']['Add usergroup IDs'] = [
    'process_put' => ['fn_exim_set_add_product_usergroups', '#key', '#this'],
    'import_only' => true,
    'linked' => false,
];

$schema['export_fields']['Usergroup IDs']['convert_put'] = array('fn_maintenance_exim_convert_usergroups', '#this');

return $schema;
