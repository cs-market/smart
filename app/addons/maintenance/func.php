<?php

use Tygh\Registry;
use Tygh\Settings;
use Tygh\Enum\UsergroupTypes;
use Tygh\Enum\YesNo;
use Tygh\Enum\ObjectStatuses;
use Tygh\Navigation\LastView\Backend;

defined('BOOTSTRAP') or die('Access denied');

include_once(__DIR__ . '/hooks.php');

function fn_maintenance_install()
{
    $setting = Settings::instance()->getSettingDataByName('log_type_general');

    if (empty($setting['variants']['debug'])) {
        $setting_id = $setting['object_id'];
        $variant_id = Settings::instance()->updateVariant(array(
            'object_id'  => $setting_id,
            'name'       => 'debug',
            'position'   => 3,
        ));

        $description = array(
            'object_id' => $variant_id,
            'object_type' => Settings::VARIANT_DESCRIPTION,
            'lang_code' => 'ru',
            'value' => 'Отладка'
        );
        Settings::instance()->updateDescription($description);
    }

    return true;
}

/* ADDON SETTINGS */
function fn_settings_variants_addons_maintenance_service_usergroups() {
    return array_column(fn_get_usergroups(['type' => UsergroupTypes::TYPE_CUSTOMER]), 'usergroup', 'usergroup_id');
}
/* /ADDON SETTINGS */


function fn_debug_log_event($data) {
    $data['backtrace'] = debug_backtrace();
    fn_log_event('general', 'debug', $data);
}

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
    static $usergroup_cache = [];
    $_cache_str = is_array($data) ? serialize($data) : $data;
    $_cache_key = md5($_cache_str);
    if (empty($usergroup_cache[$_cache_key])) {
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
        $usergroup_cache[$_cache_key] = $return;
    } else {
        $return = $usergroup_cache[$_cache_key];
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
            if ($data['message'] == $message || $data['title'] == $message) {
                unset($notifications[$key]);
            }
        }
    }
}

function fn_init_addon_override_controllers($controller, $area = AREA) {
    $controllers = array();
    static $addons = array();

    $prefix = '';
    $area_name = fn_get_area_name($area);

    $prefix = '.override';

    $addon_dir = Registry::get('config.dir.addons');

    foreach ((array) Registry::get('addons') as $addon_name => $data) {
        if ($data['status'] == 'A') {
            // try to find area-specific controller
            $dir = $addon_dir . $addon_name . '/controllers/' . $area_name . '/';

            if (is_readable($dir . $controller . $prefix . '.php')) {
                $controllers[] = $dir . $controller . $prefix . '.php';
                $addons[$addon_name] = true;
                if (empty($prefix)) {
                    fn_define('LOADED_ADDON_PATH', $addon_name);
                }
            }

            // try to find common controller
            $dir = $addon_dir . $addon_name . '/controllers/common/';
            if (is_readable($dir . $controller . $prefix . '.php')) {
                $controllers[] = $dir . $controller . $prefix . '.php';
                $addons[$addon_name] = true;
                if (empty($prefix)) {
                    fn_define('LOADED_ADDON_PATH', $addon_name);
                }
            }
        }
    }

    return array($controllers, $addons);
}

if (!is_callable('fn_find_promotion_condition')) {
    function fn_find_promotion_condition(&$conditions_group, $needle, $remove = false) {
        $res = false;
        foreach ($conditions_group['conditions'] as $i => $group_item) {
            if (isset($group_item['conditions'])) {
                $res = fn_find_promotion_condition($conditions_group['conditions'][$i], $needle, $remove);
            } elseif ((is_array($needle) && in_array($group_item['condition'], $needle)) || $group_item['condition'] == $needle) {
                if ($remove) unset($conditions_group['conditions'][$i]);
                $res = $group_item;
            }
            if ($res) return $res;
        }

        return $res;
    }
}

function fn_maintenance_exim_import_price($price, $decimals_separator = false) {
    if (is_string($price)) {
        $price = str_replace([' ', ','], ['', '.'], $price);
    }

    return (float) $price;
}

function fn_exim_get_last_view_order_ids_condition()
{
    $last_view = new Backend(AREA, 'orders', 'index');
    $view_id = $last_view->getCurrentViewId();

    $last_view_results = $last_view->getViewParams($view_id);
    
    $data_function_params = [];
    if ($last_view_results) {
        unset(
            $last_view_results['total_items'],
            $last_view_results['sort_by'],
            $last_view_results['sort_order'],
            $last_view_results['sort_order_rev'],
            $last_view_results['page'],
            $last_view_results['items_per_page']
        );
        $data_function_params = $last_view_results;
    }

    $data_function_params['get_conditions'] = true;

    list($fields, $join, $condition) = fn_get_orders($data_function_params, 0, CART_LANGUAGE);

    $order_ids = db_get_fields(
        'SELECT DISTINCT ?p' .
        ' FROM ?:orders' .
        ' ?p' .
        ' WHERE 1 = 1' .
        ' ?p',
        '?:orders.order_id',
        $join,
        $condition
    );

    return [
        'order_id' => $order_ids
    ];
}

function fn_exim_get_last_view_orders_count()
{ 
    $last_view = new Backend(AREA, 'orders', 'index');
    $view_id = $last_view->getCurrentViewId();

    $last_view_results = $last_view->getViewParams($view_id);

    if (!$last_view_results) {
        return 0;
    }

    return $last_view_results['total_items'];
}

function fn_exim_get_last_view_user_ids_condition()
{
    $last_view = new Backend(AREA, 'profiles', 'index');
    $view_id = $last_view->getCurrentViewId();

    $last_view_results = $last_view->getViewParams($view_id);
    
    $data_function_params = [];
    if ($last_view_results) {
        unset(
            $last_view_results['total_items'],
            $last_view_results['sort_by'],
            $last_view_results['sort_order'],
            $last_view_results['sort_order_rev'],
            $last_view_results['page'],
            $last_view_results['items_per_page']
        );
        $data_function_params = $last_view_results;
    }

    $data_function_params['get_conditions'] = true;

    list($fields, $join, $condition) = fn_get_users($data_function_params, Tygh::$app['session']['auth'], 0, CART_LANGUAGE);

    $user_ids = db_get_fields(
        'SELECT DISTINCT ?p' .
        ' FROM ?:users' .
        ' ?p' .
        ' WHERE 1 = 1' .
        ' ?p',
        '?:users.user_id',
        $join,
        implode(' ', $condition)
    );

    return [
        'user_id' => $user_ids
    ];
}

function fn_exim_get_last_view_users_count()
{ 
    $last_view = new Backend(AREA, 'profiles', 'index');
    $view_id = $last_view->getCurrentViewId();

    $last_view_results = $last_view->getViewParams($view_id);

    if (!$last_view_results) {
        return 0;
    }

    return $last_view_results['total_items'];
}

function fn_maintenance_build_search_condition($fields, $q, $match = 'all') {
    $condition = '';

    $q = trim($q);

    if ($match == 'any') {
        $query_pieces = fn_explode(' ', $q);
        $search_type = ' OR ';
    } elseif ($match == 'all') {
        $query_pieces = fn_explode(' ', $q);
        $search_type = ' AND ';
    } else {
        $query_pieces = [$q];
        $search_type = '';
    }

    $query_pieces = array_filter($query_pieces, 'fn_string_not_empty');

    if (!is_array($fields)) {
        $fields = [$fields];
    }

    $search_conditions = [];

    foreach ($fields as $field) {
        $tmp = [];
        foreach ($query_pieces as $piece) {
            $tmp[] = db_quote("$field LIKE ?l", '%' . $piece . '%');
        }
        $search_conditions[] = ' (' . implode($search_type, $tmp) . ') ';
    }

    $_cond = implode(' OR ', $search_conditions);

    if (!empty($search_conditions)) {
        $condition = ' AND (' . $_cond . ') ';
    }

    return $condition;
}

function fn_get_headers($key = '')
{
    $result = array();

    if (function_exists('getallheaders')) {
        $headers = getallheaders();

        foreach ($headers as $name => $value) {
            $result[$name] = $value;
        }
    } else {
        foreach ($_SERVER as $name => $value) {
            if (strncmp($name, 'HTTP_', 5) === 0) {
                $name = strtolower(str_replace('_', '-', substr($name, 5)));
                $result[$name] = $value;
            }
        }
    }

    foreach ($result as $name => $value) {
        $valid_name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
        unset($result[$name]);
        $result[$valid_name] = $value;
    }

    return empty($key) ? $result : (array_key_exists($key, $result) ? $result[$key] : '');
}
