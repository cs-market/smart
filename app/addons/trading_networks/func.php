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
use Tygh\Enum\SiteArea;
use Tygh\Enum\YesNo;
use Tygh\Enum\ProductTracking;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_init_network($request) {
    if (SiteArea::isAdmin(AREA)) {
        return array(INIT_STATUS_OK);
    }
    if (isset($request['switch_user_id']) && in_array($request['switch_user_id'], array_keys(Tygh::$app['session']['auth']['network_users']))) {
        $network_id = Tygh::$app['session']['auth']['user_id'];
        fn_login_user($request['switch_user_id'], true);
        Tygh::$app['session']['auth']['network_id'] = $network_id;
        fn_redirect(fn_url());
    }

    return array(INIT_STATUS_OK);
}

function fn_trading_networks_user_logout_after($auth) {
    if (!empty($auth['network_id'])) {
        fn_login_user($auth['network_id'], true);
    }
}

function fn_trading_networks_get_users($params, &$fields, $sortings, &$condition, $join, $auth) {
    if (isset($params['network_id'])) {
        if (empty($params['network_id'])) {
            $condition['trading_network'] = db_quote(' AND 0');
        } else {
            $condition['trading_network'] = db_quote(' AND network_id = ?i', $params['network_id']);
            $fields[] = '?:user_profiles.b_address';
            $fields[] = '?:user_profiles.s_address';
        }
    }
}

function fn_trading_networks_fill_auth(&$auth, $user_data, $area, $original_auth) {
    list($network_users) = fn_get_users(['network_id' => $auth['user_id']], $auth);
    if (!empty($network_users)) {
        $network_users = fn_array_value_to_key($network_users, 'user_id');
        $auth['network_users'] = $network_users;
    }
}

function fn_trading_networks_get_storages($params, $join, &$condition) {
    if (!empty(Tygh::$app['session']['auth']['network_users'])) {
        $condition .= ' AND 0 ';
    }
}
function fn_trading_networks_user_roles_get_list(&$roles) {
    $roles['N'] = 'trading_network';
}

function fn_trading_networks_smart_auth_auth_routines(&$pre_condition) {
    if (SiteArea::isStorefront(AREA)) {
        $pre_condition .= db_quote(' AND network_id = 0');
    }
}
