<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'create_order',
	'change_order_status',
	'delete_order',
	'update_order'
);