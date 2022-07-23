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

if (Registry::get('addons.storages.status') == 'A') {
    include_once(Registry::get('config.dir.addons') . 'storages/schemas/exim/storages_products.functions.php');

    $schema = array(
        'section' => 'storages',
        'pattern_id' => 'user_storages',
        'name' => __('storages.user_storages'),
        'key' => array('storage_id', 'user_id'),
        'table' => 'user_storages',
        'permissions' => array(
            'import' => 'manage_storages',
            'export' => 'view_storages',
        ),
        'references' => array(
            'users' => array(
                'reference_fields' => array('user_id' => '#key'),
                'join_type' => 'INNER',
            ),
            'storages' => array(
                'reference_fields' => array('storage_id' => '#key'),
                'join_type' => 'INNER',
            ),
        ),
        'options' => array(
            'remove_user_storages' => array(
                'title' => 'exim_remove_user_storages',
                'description' => 'text_remove_user_storages',
                'type' => 'checkbox',
                'import_only' => true
            ),
        ),
        'export_fields' => array (
            'Storage code' => [
                'table' => 'storages',
                'db_field' => 'code',
                'required' => true,
                'alt_key' => true,
                'convert_put' => ['fn_storages_exim_get_storage_id', '#this', '$company_id'],
            ],
            'Login' => array (
                'table' => 'users',
                'db_field' => 'user_login',
                'required' => true,
                'alt_key' => true,
                'convert_put' => ['fn_storages_exim_get_user_id', '#this', '$company_id', '@remove_user_storages'],
            ),
            'Delivery days' => array(
                'db_field' => 'delivery_date',
                'required' => true,
            ),
        ),
        'import_get_primary_object_id' => [
            'get_primary_keys' => [
                'function' => 'fn_user_storages_exim_get_primary_object_id',
                'args' => array('$alt_keys', '$skip_get_primary_object_id'),
                'import_only' => true,
            ]
        ],
        'import_process_data' => [
            'user_storage_exim_check_primary_object_id' => [
                'function'    => 'fn_user_storage_exim_check_primary_object_id',
                'args'        => ['$object', '$skip_record', '$processed_data'],
                'import_only' => true,
            ]
        ],
    );

    if (fn_allowed_for('MULTIVENDOR')) {
        if (!Registry::get('runtime.company_id')) {
            $schema['export_fields']['Company'] = array (
                'db_field' => 'company_id',
                'convert_put' => array('fn_get_company_id_by_name', '#this'),
                'import_only' => true,
            );
        }
    }
}

return $schema;
