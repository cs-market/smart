<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

function fn_maintenance_exim_set_usergroups($user_id, $data, $cleanup = true) {

    $service_usergroups = Registry::get('addons.maintenance.service_usergroups');
    if (!empty($service_usergroups)) {
        $existed_service_usergroups = db_get_array("SELECT usergroup_id, status FROM ?:usergroup_links WHERE user_id = ?i AND usergroup_id IN (?a)", $user_id, array_keys($service_usergroups));
    }

    if ($cleanup) db_query("DELETE FROM ?:usergroup_links WHERE user_id = ?i", $user_id);

    $usergroups = [];
    if (!empty($data)) {
        $usergroups = fn_maintenance_get_usergroup_ids($data, false);
    }

    if (!empty($existed_service_usergroups)) {
        $service_usergroups_formated = array_column($existed_service_usergroups, 'status', 'usergroup_id');
        $usergroups = fn_array_merge(
            $usergroups,
            $service_usergroups_formated
        );
    }

    $_data = [];
    foreach ($usergroups as $ug_id => $status) {
        $_data[] = array(
            'user_id' => $user_id,
            'usergroup_id' => $ug_id,
            'status' => $status
        );
    }
    db_query('REPLACE INTO ?:usergroup_links ?m', $_data);

    return true;
}

function fn_maintenance_exim_put_usergroup($usergroup, $lang_code) {
    $default_usergroups = fn_get_default_usergroups($lang_code);
    foreach ($default_usergroups as $usergroup_id => $ug) {
        if ($ug['usergroup'] == $usergroup) {
            return $usergroup_id;
        }
    }

    list($usergroup_id) = fn_maintenance_get_usergroup_ids($usergroup);

    return $usergroup_id ? $usergroup_id : false;
}

function fn_exim_check_usergroup($row, &$processed_data, &$skip_record) {
    if ($row['usergroup_id'] === false) {
        $skip_record = true;
        $processed_data['S']++;
    }
}

function fn_exim_set_add_product_usergroups($product_id, $data) {
    if (!empty($data)) {
        $old_usergroups = db_get_field("SELECT usergroup_ids FROM ?:products WHERE product_id = ?i", $product_id);
        $usergroup_ids = fn_maintenance_get_usergroup_ids($data);

        if ($old_usergroups) {
            $usergoups = array_unique(
                array_merge(
                    explode(',', $old_usergroups),
                    $usergroup_ids
                )
            );
        } else {
            $usergoups = $usergroup_ids;
        }

        if (!empty($usergoups)) db_query("UPDATE ?:products SET usergroup_ids = ?s WHERE product_id = ?i", implode(',', $usergoups), $product_id);
    }
}

function fn_maintenance_exim_convert_usergroups($data) {
    if ($usergroup_ids = fn_maintenance_get_usergroup_ids($data)) {
        $data = implode(',', $usergroup_ids);
    }

    return $data;
}
