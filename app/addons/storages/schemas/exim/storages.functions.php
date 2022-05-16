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

function fn_exim_set_storage_usergroups($storage_id, $data) {
    $pair_delimiter = ':';
    $set_delimiter = '; ';

    if (!empty($data)) {
        db_query("DELETE FROM ?:storage_usergroups WHERE storage_id = ?i", $storage_id);
        $usergroups = explode($set_delimiter, $data);
        if (!empty($usergroups)) {
            foreach ($usergroups as $ug) {
                $ug_data = explode($pair_delimiter, $ug);
                if (is_array($ug_data)) {
                    // Check if user group exists
                    $ug_id = db_get_field("SELECT usergroup_id FROM ?:usergroups WHERE usergroup_id = ?i", $ug_data[0]);
                    if (!empty($ug_id)) {
                        $_data = array(
                            'storage_id' => $storage_id,
                            'usergroup_id' => $ug_id
                        );

                        db_query('REPLACE INTO ?:storage_usergroups ?e', $_data);
                    }
                }
            }
        }
    }

    return true;
}

function fn_exim_get_storage_usergroups($storage_id)
{
    $set_delimiter = ';';

    $result = array();
    $usergroups = db_get_fields("SELECT usergroup_id FROM ?:storage_usergroups WHERE storage_id = ?i", $storage_id);

    return !empty($usergroups) ? implode($set_delimiter, $usergroups) : '';
}


function fn_mve_import_check_storage_data(&$v, $primary_object_id, &$options, &$processed_data, &$skip_record)
{
    if (Registry::get('runtime.company_id')) {
        $v['company_id'] = Registry::get('runtime.company_id');
    }

    return true;
}
