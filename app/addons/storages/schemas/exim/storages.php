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

include_once(Registry::get('config.dir.addons') . 'storages/schemas/exim/storages.functions.php');

$schema = array(
    'section' => 'storages',
    'pattern_id' => 'storages',
    'name' => __('storages.storages'),
    'key' => array('storage_id'),
    'table' => 'storages',
    'permissions' => array(
        'import' => 'manage_storages',
        'export' => 'view_storages',
    ),
    'export_fields' => array (
        'Storage code' => [
            'db_field' => 'code',
            'required' => true,
            'alt_key' => true,
        ],
        'Storage' => array (
            'db_field' => 'storage',
        ),
        'Company' => array (
            'db_field' => 'company_id',
            'process_get' => array('fn_get_company_name', '#this'),
            'convert_put' => array('fn_get_company_id_by_name', '#this'),
        ),
        'Min order amount' => array (
            'db_field' => 'min_order_amount',
        ),
        'Min order weight' => array (
            'db_field' => 'min_order_weight',
        ),
        'Usergroup IDs' => array(
            'process_get' => array('fn_exim_get_storage_usergroups', '#key'),
            'process_put' => array('fn_exim_set_storage_usergroups', '#key', '#this'),
            'linked' => false, // this field is not linked during import-export
        ),
        'Status' => array(
            'db_field' => 'status'
        ),
    ),
);


if (fn_allowed_for('MULTIVENDOR')) {
    if (!Registry::get('runtime.company_id')) {
        $schema['export_fields']['Company']['required'] = true;
    } else {
        $schema['import_process_data']['mve_import_check_storage_data'] = [
            'function'    => 'fn_mve_import_check_storage_data',
            'args'        => ['$object', '$primary_object_id','$options', '$processed_data', '$skip_record'],
            'import_only' => true,
        ];

        $schema['import_process_data']['fn_mve_import_check_object_id'] = [
            'function'    => 'fn_mve_import_check_object_id',
            'args'        => ['$primary_object_id', '$processed_data', '$skip_record', 'storages'],
            'import_only' => true,
        ];
    }
}

return $schema;
