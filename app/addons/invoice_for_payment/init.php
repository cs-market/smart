<?php

defined('BOOTSTRAP') or die('Access denied');

fn_register_hooks(
	'update_company_pre',
	'get_company_data_post'
);
