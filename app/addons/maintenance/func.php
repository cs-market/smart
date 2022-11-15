<?php

// use Tygh\Registry;
// use Tygh\Models\Vendor;
// use Tygh\Enum\ObjectStatuses;
// use Tygh\Enum\ProfileDataTypes;
// use Tygh\Enum\SiteArea;
// use Tygh\Enum\UserTypes;
// use Tygh\Enum\UsergroupTypes;
// use Tygh\Storage;
// use Tygh\Enum\YesNo;
// use Tygh\BlockManager\Block;

if (!defined('BOOTSTRAP')) { die('Access denied'); }


function fn_maintenance_pre_add_to_cart($product_data, &$cart, $auth, $update) {
    $cart['skip_notification'] = true;
}

function fn_maintenance_exim_get_usergroup_ids($data, $without_status = true) {
    $pair_delimiter = ':';
    $set_delimiter = ',';
    $return = [];
    if (is_array($data)) {
        $usergroups = $data;
    } else {
        $data = str_replace(';', $set_delimiter, $data);
        $usergroups = explode($set_delimiter, $data);
    }

    if (!empty($usergroups)) {
        // trim helper
        array_walk($usergroups, 'fn_trim_helper');
        foreach ($usergroups as $ug) {
            $ug_data = explode($pair_delimiter, $ug);
            if (is_array($ug_data)) {
                // Check if user group exists
                $ug_id = false;
                // search by ID
                if (is_numeric($ug_data[0])) {
                    if (in_array($ug_data[0], [USERGROUP_ALL, USERGROUP_GUEST, USERGROUP_REGISTERED])) {
                        $ug_id = $ug_data[0];
                    } elseif ($res = db_get_field("SELECT usergroup_id FROM ?:usergroups WHERE usergroup_id = ?i", $ug_data[0])) {
                        $ug_id = $res;
                    }
                }
                // search by name
                if ($ug_id === false && ($db_id = db_get_field("SELECT usergroup_id FROM ?:usergroup_descriptions WHERE usergroup = ?s AND lang_code = ?s", $ug_data[0], DESCR_SL))) {
                    $ug_id = $db_id;
                }
                if ($ug_id !== false) {
                    $return[$ug_id] = isset($ug_data[1]) ? $ug_data[1] : 'A';
                }
            }
        }
    }

    return ($without_status ? array_keys($return) : $return);
}

function fn_maintenance_promotion_get_dynamic($promotion_id, $promotion, $condition, &$cart, &$auth = NULL) {

    if ($condition == 'catalog_once_per_customer') {
        if (empty($auth['user_id'])) {
            return 'N';
        }

        // This is checkbox with values (Y/N), so we need to return appropriate values
        return fn_maintenance_promotion_check_existence($promotion_id, $cart, $auth) ? 'N' : 'Y';
    }
}

function fn_maintenance_promotion_check_existence($promotion_ids, &$cart, $auth) {
    static $statuses = null;
    if (!is_array($promotion_ids)) {
        $promotion_ids = explode(',', $promotion_ids);
    }

    if (is_null($statuses)) {
        $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true);
        foreach ($order_statuses as $status) {
            if ($status['params']['inventory'] == 'D') { // decreasing (positive) status
                $statuses[] = $status['status'];
            }
        }
    }

    if (!$statuses) {
        return false;
    }

    fn_set_hook('maintenance_promotion_check_existence', $promotion_ids, $cart, $auth);

    $exists = db_get_field(
        "SELECT order_id FROM ?:orders WHERE user_id = ?i AND (" . fn_find_array_in_set($promotion_ids, 'promotion_ids', false) . ") AND ?:orders.status IN (?a) LIMIT 1",
        $auth['user_id'], $statuses
    );

    return $exists;
}
