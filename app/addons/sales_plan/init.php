<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
	'post_delete_user',
	'delete_company',
	'create_order',
	'get_users'
);
