<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

use Tygh\Addons\ProductVariations\Product\Manager as ProductManager;

/** @var array $schema */

$schema['variations'] = function () {
    $demo_data_conf_product_id = 277; // exclude parent (configurable) product that comes with demo data
    $demo_data_var_product_ids = array(278, 279, 280, 281, 282, 283); // exclude variations that come with demo data

    $variation_id = db_get_field(
        'SELECT vp.product_id FROM ?:products AS vp'
        . ' INNER JOIN ?:products AS pp ON pp.product_id = vp.parent_product_id AND pp.product_type = ?s AND pp.status = ?s'
        . ' WHERE vp.product_type = ?s AND vp.status = ?s AND pp.product_id <> ?i AND vp.product_id NOT IN (?n) LIMIT 1',
        ProductManager::PRODUCT_TYPE_CONFIGURABLE, 'A',
        ProductManager::PRODUCT_TYPE_VARIATION, 'A',
        $demo_data_conf_product_id,
        $demo_data_var_product_ids
    );

    return !empty($variation_id);
};

return $schema;