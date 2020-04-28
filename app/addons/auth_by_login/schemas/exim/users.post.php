<?php

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'auth_by_login/schemas/exim/users.auth_by_login.functions.php');

$schema['export_fields']['Login'] = array(
	'db_field' => 'user_login',
	'alt_key' => true,
	'required' => true,
);
$schema['export_fields']['E-mail'] = array(
	'db_field' => 'email',
);

$schema['import_get_primary_object_id']['sync_email_login'] =	array(
	'function' => 'fn_exim_auth_by_login_get_primary_object_id_sync_email_login',
	'args' => array('$object', '$skip_get_primary_object_id', '$alt_keys'),
	'import_only' => true,
);

return $schema;