<?php

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'sales_plan/schemas/exim/users.functions.php');

$schema['export_fields']['Sales plan'] = [
    'process_put' => array('fn_exim_set_sales_plan', '#row', '#key'),
    'import_only' => true,
    'linked' => false,
];
$schema['export_fields']['Frequency'] = [
    'import_only' => true,
    'linked' => false,
];

return $schema;
