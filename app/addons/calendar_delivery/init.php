<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
	'get_order_info',
	'exim1c_order_xml_pre',
	'get_companies',
	'create_order',
	'update_order',
	'place_order',
	'form_cart_pre_fill',
	'update_cart_by_data_post'
);

// backward compatibility
fn_register_hooks('get_company_data');