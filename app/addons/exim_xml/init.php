<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
	'exim_csv_find_import_files_post',
	'exim_csv_import_file',
	'place_order'
);