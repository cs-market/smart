<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

if (fn_allowed_for('MULTIVENDOR') && Registry::get('runtime.company_id') ) {
    $schema['import_process_data']['mve_import_check_product_data']['function'] = 'fn_auto_exim_mve_import_check_product_data';
}

return $schema;
