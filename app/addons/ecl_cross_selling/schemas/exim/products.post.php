<?php
/*****************************************************************************
 *                                                                            *
 *                   All rights reserved! eCom Labs LLC                       *
 * http://www.ecom-labs.com/about-us/ecom-labs-modules-license-agreement.html *
 *                                                                            *
 *****************************************************************************/

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'ecl_cross_selling/schemas/exim/products.functions.php');

$schema['export_fields']['Related products'] = array (
    'process_get' => array('fn_ecl_exim_get_related_products', '#key'),
    'process_put' => array('fn_ecl_exim_set_related_products', '#key', '#this'),
    'multilang' => false,
    'linked' => false,
    'default' => ''
);

return $schema;
