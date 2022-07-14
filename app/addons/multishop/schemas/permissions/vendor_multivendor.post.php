<?php

$schema['controllers']['shops']['permissions'] = true;
$schema['controllers']['block_manager']['permissions'] = true;
foreach ($schema['controllers']['block_manager']['modes'] as &$mode) {
	$mode = array('permissions' => true);
}

return $schema;
