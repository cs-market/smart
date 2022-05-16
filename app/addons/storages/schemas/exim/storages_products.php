<?php
/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*      Copyright (c) 2013 CS-Market Ltd. All rights reserved.             *
*                                                                         *
*  This is commercial software, only users who have purchased a valid     *
*  license and accept to the terms of the License Agreement can install   *
*  and use this program.                                                  *
*                                                                         *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*  PLEASE READ THE FULL TEXT OF THE SOFTWARE LICENSE AGREEMENT IN THE     *
*  "license agreement.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.  *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * **/

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'storages/schemas/exim/storages_products.functions.php');
/// cleanup old prices
$schema = array(
    'section' => 'storages',
    'pattern_id' => 'storages_products',
    'name' => __('storages_products'),
    'key' => array('storage_id', 'product_id'),
    'table' => 'storages_products',
    'permissions' => array(
        'import' => 'manage_storages',
        'export' => 'view_storages',
    ),
    'references' => array(
        'products' => array(
            'reference_fields' => array('product_id' => '#key'),
            'join_type' => 'INNER',
        ),
        'storages' => array(
            'reference_fields' => array('storage_id' => '#key'),
            'join_type' => 'INNER',
        ),
    ),
    'export_fields' => array (
        'Storage code' => [
            'table' => 'storages',
            'db_field' => 'code',
            'required' => true,
            'alt_key' => true,
            'convert_put' => ['fn_storages_exim_get_storage_id', '#this'],
        ],
        'Product code' => array (
            'table' => 'products',
            'db_field' => 'product_code',
            'required' => true,
            'alt_key' => true,
            'convert_put' => ['fn_storages_exim_get_product_id', '#this'],
        ),
        'Quantity' => array (
            'db_field' => 'amount',
        ),
        'Min quantity' => array(
            'db_field' => 'min_qty',
        ),
        'Quantity step' => array(
            'db_field' => 'qty_step',
        ),
    ),
    'import_get_primary_object_id' => [
        'get_primary_keys' => [
            'function' => 'fn_storages_products_exim_get_primary_object_id',
            'args' => array('$alt_keys', '$skip_get_primary_object_id'),
            'import_only' => true,
        ]
    ],
    'import_process_data' => [
        'storage_exim_check_primary_object_id' => [
            'function'    => 'fn_storage_exim_check_primary_object_id',
            'args'        => ['$object', '$skip_record', '$processed_data'],
            'import_only' => true,
        ]
    ],
);

if (fn_allowed_for('MULTIVENDOR')) {
    if (!Registry::get('runtime.company_id')) {
        //         'Company' => array (
        //     'db_field' => 'company_id',
        //     'process_get' => array('fn_get_company_name', '#this'),
        //     'convert_put' => array('fn_get_company_id_by_name', '#this'),
        // ),
    }
}

return $schema;
