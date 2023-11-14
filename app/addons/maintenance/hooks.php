<?php

use Tygh\Registry;
use Tygh\Enum\SiteArea;
use Tygh\Enum\UserTypes;
use Tygh\Enum\UsergroupStatuses;
use Tygh\Enum\ProductFilterProductFieldTypes;
use Tygh\Enum\ObjectStatuses;
use Tygh\Enum\YesNo;
use Tygh\Tools\SecurityHelper;

defined('BOOTSTRAP') or die('Access denied');

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

function fn_maintenance_get_promotions($params, &$fields, $sortings, &$condition, $join, $group, $lang_code) {
    if (defined('ORDER_MANAGEMENT') && !empty($params['promotion_id'])) {
        return;
    }
    if (!empty($params['fields'])) {
        if (!is_array($params['fields'])) {
            $params['fields'] = explode(',', $params['fields']);
        }
        $fields = $params['fields'];
    }
    if (!empty($params['exclude_promotion_ids'])) {
        if (!is_array($params['exclude_promotion_ids'])) $params['exclude_promotion_ids'] = [$params['exclude_promotion_ids']];
        $condition .= db_quote(' AND ?:promotions.promotion_id NOT IN (?a)', $params['exclude_promotion_ids']);
    }
}

function fn_maintenance_dispatch_assign_template($controller, $mode, $area, &$controllers_cascade) {
    $root_dir = Registry::get('config.dir.root') . '/app';
    $addon_dir = Registry::get('config.dir.addons');
    $addons = (array) Registry::get('addons');
    $area_name = fn_get_area_name($area);
    foreach ($controllers_cascade as &$ctrl) {
        $path = str_replace([$root_dir, '/controllers'], ['', ''], $ctrl);
        foreach ($addons as $addon_name => $data) {
            if ($data['status'] == 'A') {
                $dir = $addon_dir . $addon_name . '/controllers/overrides';
                if (is_readable($dir . $path)) {
                    $ctrl = $dir . $path;
                }
            }
        }
    }
    unset($crtl);
}

function fn_maintenance_check_permission_manage_profiles(&$result, $user_type) {
    $can_manage_profiles = true;

    if (Registry::get('runtime.company_id')) {
        $can_manage_profiles = (in_array($user_type, [UserTypes::CUSTOMER, UserTypes::VENDOR])) && Registry::get('runtime.company_id');
    }

    $result = $can_manage_profiles;
}

function fn_maintenance_check_rights_delete_user($user_data, $auth, &$result) {
    $result = true;

    if (
        ($user_data['is_root'] == 'Y' && !$user_data['company_id']) // root admin
        || (!empty($auth['user_id']) && $auth['user_id'] == $user_data['user_id']) // trying to delete himself
        || (Registry::get('runtime.company_id') && $user_data['is_root'] == 'Y') // vendor root admin
        || (Registry::get('runtime.company_id') && fn_allowed_for('ULTIMATE') && $user_data['company_id'] != Registry::get('runtime.company_id')) // user from other store
    ) {
        $result = false;
    }
}

function fn_maintenance_get_users($params, $fields, $sortings, &$condition, $join, $auth) {
    if ((!isset($params['user_type']) || UserTypes::isAdmin($params['user_type'])) && fn_is_restricted_admin(['user_type' => $auth['user_type']])) {
        $condition['wo_root_admins'] .= db_quote(' AND is_root != ?s ', YesNo::YES);
    }

    if (isset($params['address']) && fn_string_not_empty($params['address'])) {
        $condition['address'] = fn_maintenance_build_search_condition(['?:user_profiles.b_address', '?:user_profiles.s_address'], $params['address'], 'all');
    }

    if (isset($params['name']) && fn_string_not_empty($params['name'])) {
        $name_fields = ['?:users.firstname', '?:users.lastname'];
        if (!$params['extended_search'] && isset($params['search_query'])) {
            $name_fields = array_merge($name_fields, ['?:users.email', '?:users.phone', '?:user_profiles.b_phone', '?:user_profiles.s_phone']);
        }
        $condition['name'] = fn_maintenance_build_search_condition($name_fields, $params['name'], 'all');
    }
}

function fn_maintenance_mailer_create_message_before($_this, &$message, $area, $lang_code, $transport, $builder) {
    // DO NOT TRY TO SEND EMAILS TO @example.com
    if (!empty($message['to'])) {
        if (is_array($message['to'])) {
            $message['to'] = array_filter($message['to'], function($v) {
                return strpos($v, '@example.com') === false;
            });
        } elseif (is_string($message['to'])) {
            $message['to'] = (strpos($message['to'], '@example.com') === false) ? $message['to'] : '';
        }
    }
}

function fn_maintenance_get_payments_pre(&$params) {
    if (defined('ORDER_MANAGEMENT')) {
        $params['status'] = 'A';
    }
}

function fn_maintenance_shippings_get_shippings_list_conditions($group, $shippings, $fields, $join, &$condition, $order_by) {
    if (defined('ORDER_MANAGEMENT')) {
        $condition .= " AND (" . fn_find_array_in_set(\Tygh::$app['session']['customer_auth']['usergroup_ids'], '?:shippings.usergroup_ids', true) . ")";
    }
}

function fn_maintenance_get_user_short_info_pre($user_id, $fields, &$condition, $join, $group_by) {
    $condition = str_replace("AND status = 'A'", ' ', $condition);
}

function fn_maintenance_save_log($type, $action, $data, $user_id, &$content, $event_type, $object_primary_keys) {
    if ($type == 'general' && $action == 'debug') {
        foreach ($data as $key => $value) {
            if ($key == 'backtrace') continue;
            if (is_array($value)) {
                $content[$key] = serialize($value);
            } else {
                $content[$key] = $value;
            }
        }
        $content = array_filter($content);
    }
}

function fn_maintenance_pre_get_orders($params, &$fields, $sortings, $get_totals, $lang_code) {
    $fields[] = 'tracking_link';
}

function fn_maintenance_development_show_stub($placeholders, $append, &$content, $is_error) {
    $content = '<img style="margin: 40px auto; display: block;" src="design/themes/responsive/media/images/addons/maintenance/stub.jpg">';
}

function fn_maintenance_get_carts($type_restrictions, $params, $condition, &$join, $fields, $group) {
    if (fn_allowed_for('MULTIVENDOR') && $company_id = Registry::get('runtime.company_id')) {
        $join .= db_quote(' RIGHT JOIN ?:users AS u ON u.user_id = ?:user_session_products.user_id AND ?:user_session_products.user_type = ?s AND u.company_id = ?i', 'R', $company_id);
    }
}

/**
 * TODO
 * 
 * add to core function fn_change_usergroup_status after $is_available_status 
 * fn_set_hook('change_usergroup_status_pre', $status, $user_id, $usergroup_id, $force_notification, $is_available_status);
 * 
 * replace in function fn_promotion_post_processing near $is_ug_already_assigned 
 * db_query("REPLACE INTO ?:usergroup_links SET user_id = ?i, usergroup_id = ?i, status = 'A'", $order_info['user_id'], $bonus['value']);
 * by
 * fn_change_usergroup_status("A", $order_info['user_id'], $bonus['value']);
 * and
 * db_query("UPDATE ?:usergroup_links SET status = 'F' WHERE user_id = ?i AND usergroup_id = ?i", $order_info['user_id'], $bonus['value']);
 * by
 * fn_change_usergroup_status("F", $order_info['user_id'], $bonus['value']);
 * 
 */

function fn_maintenance_change_usergroup_status_pre($status, &$user_id, $usergroup_id, $force_notification, &$is_available_status) {
    $service_usergroups = Registry::get('addons.maintenance.service_usergroups');

    if (!empty($service_usergroups) && array_key_exists($usergroup_id, $service_usergroups) && $status != UsergroupStatuses::ACTIVE) {
        $is_available_status = false;
        $user_id = false; // in order to fn_check_usergroup_available_for_user return false
    }
}

function fn_maintenance_get_product_filter_fields(&$filters) {
    $filters[ProductFilterProductFieldTypes::PRICE]['conditions'] = function($db_field, $join, $condition) {

        $join .= db_quote("
            LEFT JOIN ?:product_prices as prices_2 ON ?:product_prices.product_id = prices_2.product_id AND ?:product_prices.price > prices_2.price AND prices_2.lower_limit = 1 AND prices_2.usergroup_id IN (?n)",
            array_filter(Tygh::$app['session']['auth']['usergroup_ids'])
        );

        $condition .= db_quote("
            AND ?:product_prices.lower_limit = 1 AND ?:product_prices.usergroup_id IN (?n) AND prices_2.price IS NULL",
            array_filter(Tygh::$app['session']['auth']['usergroup_ids'])
        );

        if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
            $db_field = "IF(shared_prices.product_id IS NOT NULL, shared_prices.price, ?:product_prices.price)";
            $join .= db_quote(" LEFT JOIN ?:ult_product_prices AS shared_prices ON shared_prices.product_id = products.product_id"
                . " AND shared_prices.lower_limit = 1"
                . " AND shared_prices.usergroup_id IN (?n)"
                . " AND shared_prices.company_id = ?i",
                array_merge(array(USERGROUP_ALL), Tygh::$app['session']['auth']['usergroup_ids']),
                Registry::get('runtime.company_id')
            );
        }

        return array($db_field, $join, $condition);
    };
}

function fn_maintenance_get_products(&$params, &$fields, $sortings, &$condition, &$join, $sorting, $group_by, $lang_code, $having) {
    // fix product variations: free space should be into condition
    if (strpos($condition, 'AND 1 != 1')) {
        $condition = str_replace('AND 1 != 1', ' AND 1 != 1', $condition);
    }

    if (SiteArea::isAdmin(AREA)) {
        $fields['timestamp'] = "products.timestamp";
        if (isset($params['product_code']) && !empty($params['product_code'])) {
            $condition .= db_quote(" AND products.product_code LIKE ?l", trim($params['product_code']));
        }
    }

    if (SiteArea::isStorefront(AREA)) {
        // do not show products for unlogged users
        $condition .= db_quote(' AND products.usergroup_ids != ?s', '');

        //for sorting by price
        $auth = Tygh::$app['session']['auth'];

        $remove_join = " LEFT JOIN ?:product_prices as prices ON prices.product_id = products.product_id AND prices.lower_limit = 1";
        $add_join = db_quote(" LEFT JOIN ?:product_prices as prices ON prices.product_id = products.product_id AND prices.lower_limit = 1 AND usergroup_id IN (?a)",  array_filter($auth['usergroup_ids']));
        $join = str_replace($remove_join, $add_join, $join);

        $regular_price_field = isset($fields['price']) ? str_replace(' as price', '', $fields['price']) : false;

        if (!empty($regular_price_field)) {
            $join .= ' LEFT JOIN ?:product_prices as reg_prices ON reg_prices.product_id = products.product_id AND reg_prices.lower_limit = 1 AND reg_prices.usergroup_id = 0 ';
            $fields['price'] = db_quote(
                'IF('.$regular_price_field.' IS NOT NULL, '.$regular_price_field .', reg_prices.price) as price'
            );
        }

        $remove_condition = db_quote(' AND prices.usergroup_id IN (?n)', (($params['area'] == 'A') ? USERGROUP_ALL : array_merge(array(USERGROUP_ALL), $auth['usergroup_ids'])));
        //need to move to join
        //$add_condition = db_quote(' AND prices.usergroup_id IN (?n)', (($params['area'] == 'A') ? USERGROUP_ALL : array_filter($auth['usergroup_ids'])));
        $condition = str_replace($remove_condition, ''/*$add_condition*/, $condition);

        if (in_array('prices2', $params['extend'])) {
            $remove_join = db_quote(' AND prices_2.usergroup_id IN (?n)', (($params['area'] == 'A') ? USERGROUP_ALL : array_merge(array(USERGROUP_ALL), $auth['usergroup_ids'])));
            $add_join = db_quote(' AND prices_2.usergroup_id IN (?n)', (($params['area'] == 'A') ? USERGROUP_ALL : array_filter($auth['usergroup_ids'])));
            $join = str_replace($remove_join, $add_join, $join);
        }
    }

    if (!empty($params['exclude_cid'])) {
        if (!is_array($params['exclude_cid'])) $params['exclude_cid'] = explode(',', $params['exclude_cid']);
        $condition .= db_quote(" AND ?:categories.category_id NOT IN (?n)", $params['exclude_cid']);
    }
}

function fn_maintenance_user_init($auth, $user_info, $first_init) {
    if (!defined('API') && !fn_get_cookie('device-id')) {
        fn_set_cookie('device-id', USER_AGENT . '-' . substr(SecurityHelper::generateRandomString(), 0, 8));
    }
}

function fn_maintenance_create_order(&$order) {
    $order['device_id'] = (defined('API')) ? fn_get_headers('Device-Id') : fn_get_cookie('device-id');
}
