<?php

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'smart_distribution/schemas/exim/products.functions.php');

$schema['export_fields']['Send price to 1c'] = array (
    'db_field' => 'send_price_1c',
);

$schema['export_fields']['Detailed image']['process_put'] = ['fn_exim_smart_distribution_import_images', '@images_path', '%Thumbnail%', '#this', '0', 'M', '#key', 'product'];

$schema['export_fields']['Category']['process_put'][0] = 'fn_exim_sd_set_product_categories';
$schema['export_fields']['Category']['default'] = '!Потерянные товары';
$schema['export_fields']['Secondary categories']['process_put'][0] = 'fn_exim_sd_set_product_categories';
if (fn_allowed_for('MULTIVENDOR') && (!Registry::get('runtime.company_id'))) {
	$schema['export_fields']['Category']['process_put'][] = '%Vendor%';
	$schema['export_fields']['Secondary categories']['process_put'][] = '%Vendor%';
}

$schema['export_fields']['Add user group IDs'] = [
    'process_put' => array('fn_exim_set_add_product_usergroups', '#key', '#this'),
    'import_only' => true,
    'linked' => false,
];

return $schema;
