<?php

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'smart_distribution/schemas/exim/qty_discounts.functions.php');

$schema['import_get_primary_object_id'] = array(
    'fill_primary_object_company_id' => array(
        'function' => 'fn_exim_apply_company',
        'args' => array('$pattern', '$alt_keys', '$object', '$skip_get_primary_object_id'),
        'import_only' => true,
    ),
);

// fix usergroup creation by vendor
$schema['export_fields']['User group']['convert_put'][0] = 'fn_exim_mve_put_usergroup';
$schema['']['User group']['pre_insert'] = array('fn_exim_mve_check_usergroup', '#this');

$schema['import_process_data'] = array(
    'check_usergroup' => array(
        'function' => 'fn_exim_mve_check_usergroup',
        'args' => array('$object', '$processed_data', '$skip_record'),
        'import_only' => true,
    ),
);

return $schema;