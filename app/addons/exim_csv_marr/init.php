<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
	'export_order_to_csv',
	'exim_csv_find_csvs'
);