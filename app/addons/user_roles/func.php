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

use Tygh\Enum\UserRoles;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_get_user_role_list($user_type = '') {
    return UserRoles::getList($user_type);
}

function fn_user_roles_get_users_pre(&$params, $auth, $items_per_page, $custom_view) {
    if (!empty($params['user_role'])) {
        unset($params['exclude_user_types']);
    }
}

function fn_user_roles_get_users(&$params, &$fields, $sortings, &$condition, $join, $auth) {
    if (!empty($params['user_role'])) {
        if (!is_array($params['user_role'])) $params['user_role'] = explode(',', $params['user_role']);
        $condition['user_role'] = db_quote(' AND user_role IN (?a)', $params['user_role']);
        unset($params['exclude_user_types']);
        $fields['user_role'] = '?:users.user_role';
    }
}

function fn_user_roles_fill_auth(&$auth, $user_data, $area, $original_auth) {
    if (empty($auth['user_role']) && !empty($user_data['user_role'])) {
        $auth['user_role'] = $user_data['user_role'];
    }
}

function fn_user_roles_user_init(&$auth, $user_info) {
    if (empty($auth['user_role']) && !empty($user_info['user_role'])) {
        $auth['user_role'] = $user_info['user_role'];
    }
}

function fn_user_roles_get_user_short_info_pre($user_id, &$fields, $condition, $join, $group_by) {
    $fields[] = 'user_role';
}

if (!is_callable('fn_array_group')) {
    function fn_array_group(array $array, $key)
    {
        if (!is_string($key) && !is_int($key) && !is_float($key) && !is_callable($key) ) {
            trigger_error('fn_array_group(): The key should be a string, an integer, or a callback', E_USER_ERROR);
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
                $grouped[$key] = call_user_func_array('fn_array_group', $params);
            }
        }
        return $grouped;
    }
}
