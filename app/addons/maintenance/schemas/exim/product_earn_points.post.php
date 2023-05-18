<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

include_once(Registry::get('config.dir.addons') . 'maintenance/schemas/exim/exim.functions.php');

$schema['export_fields']['Usergroup IDs']['convert_put'][0] = 'fn_maintenance_exim_convert_usergroups';

return $schema;
