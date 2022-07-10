<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
	'init_company_id'
);

fn_init_stack(array('fn_multishop_init_store_params_by_host', &$_REQUEST));
