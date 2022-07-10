<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
	'dispatch_assign_template',
	'get_categories',
	'get_category_data',
	'layout_get_default',
	'layout_get_list',
	'layout_update_pre',
	'get_theme_path_pre',
	'get_theme_path'
);