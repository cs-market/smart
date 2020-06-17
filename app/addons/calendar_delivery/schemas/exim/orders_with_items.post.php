<?php

$schema['export_fields']['Delivery date'] = array(
	'db_field' => 'delivery_date',
	'process_get' => array('fn_timestamp_to_date', '#this'),
);

$schema['export_fields']['Delivery period'] = array(
	'db_field' => 'delivery_period',
);

return $schema;
