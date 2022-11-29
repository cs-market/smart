<?php

// use Tygh\Registry;
// use Tygh\Models\Vendor;
// use Tygh\Enum\ObjectStatuses;
// use Tygh\Enum\ProfileDataTypes;
use Tygh\Enum\SiteArea;
// use Tygh\Enum\UserTypes;
// use Tygh\Enum\UsergroupTypes;
// use Tygh\Storage;
use Tygh\Enum\YesNo;
// use Tygh\BlockManager\Block;
use Tygh\Enum\ObjectStatuses;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/* HOOKS */
function fn_maintenance_pre_add_to_cart($product_data, &$cart, $auth, $update) {
    $cart['skip_notification'] = true;
}

function fn_maintenance_update_storage_usergroups_pre(&$storage_data) {
    $storage_data['usergroup_ids'] = fn_maintenance_get_usergroup_ids($storage_data['usergroup_ids']);
}

function fn_maintenance_update_product_prices($product_id, &$_product_data, $company_id, $skip_price_delete, $table_name, $condition) {
    foreach ($_product_data['prices'] as &$v) {
        $v['product_id'] = $product_id;
        $v['lower_limit'] = $v['lower_limit'] ?? 1;
        if (isset($v['usergroup_id']) && !is_numeric($v['usergroup_id'])) {
            list($v['usergroup_id']) = fn_maintenance_get_usergroup_ids($v['usergroup_id']);
        }
    }
}

function fn_maintenance_update_product_pre(&$product_data) {
    if (isset($product_data['usergroup_ids']) && !empty($product_data['usergroup_ids'])) {
        $product_data['usergroup_ids'] = fn_maintenance_get_usergroup_ids($product_data['usergroup_ids']);
    }
}

function fn_maintenance_update_profile($action, $user_data, $current_user_data) {
    if ((($action == 'add' && SiteArea::isStorefront(AREA)) || defined('API')) && !empty($user_data['usergroup_ids'])) {
        $user_data['usergroup_ids'] = fn_maintenance_get_usergroup_ids($user_data['usergroup_ids']);
        db_query('DELETE FROM ?:usergroup_links WHERE user_id = ?i', $user_data['user_id']);
        foreach ($user_data['usergroup_ids'] as $ug_id) {
            fn_change_usergroup_status(ObjectStatuses::ACTIVE, $user_data['user_id'], $ug_id);
        }
    }
}

/* END HOOKS */

function fn_maintenance_promotion_get_dynamic($promotion_id, $promotion, $condition, &$cart, &$auth = NULL) {
    if ($condition == 'catalog_once_per_customer') {
        if (empty($auth['user_id'])) {
            return YesNo::NO;
        }

        // This is checkbox with values (Y/N), so we need to return appropriate values
        return fn_maintenance_promotion_check_existence($promotion_id, $cart, $auth) ? YesNo::NO : YesNo::YES;
    }
}

function fn_maintenance_promotion_check_existence($promotion_ids, &$cart, $auth) {
    static $order_statuses = null;
    if (!is_array($promotion_ids)) {
        $promotion_ids = explode(',', $promotion_ids);
    }

    if (is_null($order_statuses)) {
        $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true);
        $order_statuses = array_filter($order_statuses, function($v) {
            return YesNo::toBool($v['params']['payment_received']);
        });
    }

    if (!$order_statuses) {
        return false;
    }

    fn_set_hook('maintenance_promotion_check_existence', $promotion_ids, $cart, $auth);

    $exists = db_get_field("SELECT order_id FROM ?:orders WHERE user_id = ?i AND (" . fn_find_array_in_set($promotion_ids, 'promotion_ids', false) . ") AND ?:orders.status IN (?a) LIMIT 1", $auth['user_id'], array_keys($order_statuses));

    return $exists;
}

function fn_maintenance_get_usergroup_ids($data, $without_status = true) {
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
        array_walk($usergroups, 'fn_trim_helper');
        foreach ($usergroups as $ug) {
            $ug_data = fn_explode($pair_delimiter, $ug);
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
                    $return[$ug_id] = isset($ug_data[1]) ? $ug_data[1] : ObjectStatuses::ACTIVE;
                }
            }
        }
    }

    return ($without_status ? array_keys($return) : $return);
}

// use this instead of fn_array_group
function fn_group_array_by_key(array $array, $key)
{
    if (!is_string($key) && !is_int($key) && !is_float($key) && !is_callable($key) ) {
        trigger_error('array_group_by(): The key should be a string, an integer, or a callback', E_USER_ERROR);
        return null;
    }
    $func = (!is_string($key) && is_callable($key) ? $key : null);
    $_key = $key;
    // Load the new array, splitting by the target key
    $grouped = [];
    foreach ($array as $value) {
        $key = null;
        if (is_callable($func)) {
            $key = call_user_func($func, $value);
        } elseif (is_object($value) && property_exists($value, $_key)) {
            $key = $value->{$_key};
        } elseif (isset($value[$_key])) {
            $key = $value[$_key];
        }
        if ($key === null) {
            continue;
        }
        $grouped[$key][] = $value;
    }
    // Recursively build a nested grouping if more parameters are supplied
    // Each grouped array value is grouped according to the next sequential key
    if (func_num_args() > 2) {
        $args = func_get_args();
        foreach ($grouped as $key => $value) {
            $params = array_merge([ $value ], array_slice($args, 2, func_num_args()));
            $grouped[$key] = call_user_func_array('fn_group_array_by_key', $params);
        }
    }
    return $grouped;
}

function fn_delete_notification_by_message($message) {
    $notifications = &Tygh::$app['session']['notifications'];

    if (!empty($notifications)) {
        foreach ($notifications as $key => $data) {
            if ($data['message'] == $message) {
                unset($notifications[$key]);
            }
        }
    }
}