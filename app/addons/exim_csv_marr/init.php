<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'export_order_to_csv',
    'auto_exim_find_files'
);