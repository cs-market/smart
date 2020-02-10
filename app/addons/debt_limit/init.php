<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
	'user_init',
	'exim_1c_update_order'
);
