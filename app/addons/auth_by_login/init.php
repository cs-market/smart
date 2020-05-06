<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
	'auth_routines',
	'is_user_exists_pre',
	'user_exist'
);
