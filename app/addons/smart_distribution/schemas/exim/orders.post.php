<?php

$schema['export_fields']['1c'] = array(
	'linked' => false,
	'process_get' => array('fn_get_1c_code', '#key')
);

function fn_get_1c_code($oid) {
	$oi = fn_get_order_info($oid);
	return (empty($oi['fields']['38'])) ? '' : $oi['fields']['38'];
}

return $schema;