<?php

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'trading_networks/schemas/exim/users.functions.php');

$schema['export_fields']['Trading network'] = [
    'process_get' => array('fn_user_id_to_login', '#this'),
    'convert_put' => array('fn_login_to_user_id', '#this', '#row'),
    'db_field' => 'network_id',
];

return $schema;
