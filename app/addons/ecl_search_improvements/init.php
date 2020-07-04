<?php
/*****************************************************************************
*                                                                            *
*                   All rights reserved! eCom Labs LLC                       *
* http://www.ecom-labs.com/about-us/ecom-labs-modules-license-agreement.html *
*                                                                            *
*****************************************************************************/

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'additional_fields_in_search',
	'get_users',
    'get_orders',
    'get_search_objects_post'
);
