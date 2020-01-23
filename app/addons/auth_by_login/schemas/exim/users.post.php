<?php

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'auth_by_login/schemas/exim/users.auth_by_login.functions.php');

$schema['export_fields']['Login']['db_field'] = 'user_login';
$schema['export_fields']['Login']['alt_key'] = true;

$schema['export_fields']['E-mail']['required'] = false;

$schema['import_process_data']['sync_email_login'] =	array(
	'function' => 'fn_exim_auth_by_login_sync_email_login',
	'args' => array('$object', '$skip_record', '$processed_data'),
	'import_only' => true,
);

$schema['import_get_primary_object_id']['sync_email_login'] =	array(
	'function' => 'fn_exim_auth_by_login_get_primary_object_id_sync_email_login',
	'args' => array('$object', '$skip_get_primary_object_id'),
	'import_only' => true,
);

return $schema;
