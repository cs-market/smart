<?php

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'smart_distribution/schemas/exim/orders.functions.php');

$schema['export_fields']['1c'] = array(
	'linked' => false,
	'process_get' => array('fn_get_1c_code', '#key')
);

return $schema;