<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
	'init_company_id'
);
$stack = Registry::get('init_stack');
foreach ($stack as &$stack_data) {
    if ($stack_data[0] != 'fn_init_http_params_by_storefront') continue;
    $stack_data[0] = 'fn_multishop_init_store_params_by_host';
    break;
}
Registry::set('init_stack', $stack);
