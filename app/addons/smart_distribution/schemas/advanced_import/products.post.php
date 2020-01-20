<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

include_once(Registry::get('config.dir.addons') . 'smart_distribution/schemas/exim/products.functions.php');

$schema['export_fields']['Detailed image']['process_put'] = ['fn_exim_smart_distribution_import_images', '@images_path', '%Thumbnail%', '#this', '0', 'M', '#key', 'product'];

return $schema;
