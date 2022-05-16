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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_get_storages($params, $items_per_page = 0) {
    $condition = '';

    if (!empty($params['status'])) {
        $condition .= db_quote(' AND status = ?s', $params['status']);
    }

    if (Registry::get('runtime.company_id')) {
        $params['company_id'] = Registry::get('runtime.company_id');
    }

    if (isset($params['company_id'])) {
        $condition .= db_quote(" AND company_id = ?i", $params['company_id']);
    }

    if (isset($params['storage_id'])) {
        $condition .= db_quote(' AND storage_id = ?i', $params['storage_id']);
    }

    if (!empty($params['storage_ids'])) {
        if (!is_array($params['storage_ids'])) {
            $params['storage_ids'] = explode(',', $params['storage_ids']);
        }
        $condition .= db_quote(' AND storage_id IN (?a)', $params['storage_ids']);
    }

    $storages = db_get_hash_array("SELECT * FROM ?:storages WHERE 1 ?p", 'storage_id', $condition);

    if (isset($params['storage_id'])) {
        foreach ($storages as &$storage) {
            $storage['usergroup_ids'] = db_get_fields('SELECT usergroup_id FROM ?:storage_usergroups WHERE storage_id = ?i', $storage['storage_id']);
        }
    }

    return [$storages, $params];
}

function fn_update_storage($storage_data, $storage_id = 0) {
    unset($storage_data['storage_id']);

    if (fn_allowed_for('MULTIVENDOR') && Registry::get('runtime.company_id')) {
        $storage_data['company_id'] = $storage_data['company_id'] ?? Registry::get('runtime.company_id');
        if (!empty($storage_id) && !($storage_id = db_get_field('SELECT storage_id FROM ?:storages WHERE storage_id = ?i AND company_id = ?i', $storage_id, $storage_data['company_id']))) {
            return false;
        }
    }

    if (!empty($storage_id)) {
        db_query("UPDATE ?:storages SET ?u WHERE storage_id = ?i", $storage_data, $storage_id);
    } else {
        $storage_data['storage_id'] = $storage_id = db_query("INSERT INTO ?:storages ?e", $storage_data);
    }

    if (isset($storage_data['usergroup_ids'])) {
        db_query("DELETE FROM ?:storage_usergroups WHERE storage_id = ?i", $storage_id);
        $usergroups_data = [];

        fn_set_hook('update_storage_usergroups_pre', $storage_data);

        foreach ($storage_data['usergroup_ids'] as $usergroup_id) {
            $usergroups_data[] = ['storage_id' => $storage_id, 'usergroup_id' => $usergroup_id];
        }
        if ($usergroups_data) db_query('INSERT INTO ?:storage_usergroups ?m', $usergroups_data);
    }

    return $storage_id;
}

function fn_delete_storages($storage_ids) {
    if (!is_array($storage_ids)) {
        $storage_ids = explode(',', $storage_ids);
    }

    db_query("DELETE FROM ?:storages WHERE storage_id IN (?n)", $storage_ids);
    db_query("DELETE FROM ?:storages_products WHERE storage_id IN (?n)", $storage_ids);
    db_query("DELETE FROM ?:storage_usergroups WHERE storage_id IN (?n)", $storage_ids);

    fn_set_hook('delete_storages', $storage_ids);
}

function fn_storages_get_usergroups_pre(&$params, $lang_code) {
    if (fn_allowed_for('MULTIVENDOR')) {
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $usergroup_ids = db_get_field("SELECT usergroup_ids FROM ?:vendor_plans LEFT JOIN ?:companies ON ?:companies.plan_id = ?:vendor_plans.plan_id WHERE company_id = ?i", $params['company_id']);
            if (!empty($usergroup_ids)) $params['usergroup_id'] = explode(',',$usergroup_ids);
        }
    }
}

function fn_storages_update_product_post($product_data, $product_id, $lang_code, $create) {
    if (isset($product_data['storages'])) {
        db_query('DELETE FROM ?:storages_products WHERE product_id = ?i', $product_id);
        foreach ($product_data['storages'] as $storage_id => &$storage_data) {
            if (empty(array_filter($storage_data))) {
                unset($product_data['storages'][$storage_id]);
            } else {
                $storage_data['storage_id'] = $storage_id;
                $storage_data['product_id'] = $product_id;
            }
        }
        if ($product_data['storages']) db_query('INSERT INTO ?:storages_products ?m', $product_data['storages']);
    }
}

function fn_get_storages_amount($product_id) {
    $return = [];
    if ($product_id) {
        $return = db_get_hash_array('SELECT * FROM ?:storages_products WHERE product_id = ?i', 'storage_id', $product_id);
    }

    return $return;
}
