<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
	'get_companies',
	'get_usergroups',
	'get_user_info'
);