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

function fn_maintenance_get_users(&$params, &$fields, &$sortings, &$condition, &$join, $auth) {
    if ((!isset($params['user_type']) || UserTypes::isAdmin($params['user_type'])) && fn_is_restricted_admin(['user_type' => $auth['user_type']])) {
        $condition['wo_root_admins'] .= db_quote(' AND is_root != ?s ', YesNo::YES);
    }

    if (isset($params['address']) && fn_string_not_empty($params['address'])) {
        $condition['address'] = fn_maintenance_build_search_condition(['?:user_profiles.b_address', '?:user_profiles.s_address'], $params['address'], 'all');
    }

    if (isset($params['name']) && fn_string_not_empty($params['name'])) {
        $name_fields = ['?:users.user_login', '?:users.firstname', '?:users.lastname'];
        if (!$params['extended_search'] && isset($params['search_query'])) {
            $name_fields = array_merge($name_fields, ['?:users.email', '?:users.phone', '?:user_profiles.b_phone', '?:user_profiles.s_phone']);
        }
        $condition['name'] = fn_maintenance_build_search_condition($name_fields, $params['name'], 'all');
    }

    $without_order_prefix = 'orders_period_';
    if (!empty($params['user_orders'])) {
        list(
            $w_time_from,
            $w_time_to,
        ) = [
            $without_order_prefix . 'time_from',
            $without_order_prefix . 'time_to',
        ];

        list($params[$w_time_from], $params[$w_time_to]) = fn_create_periods([
            'period' => $params[$without_order_prefix . 'period'],
            'time_from' => $params[$w_time_from],
            'time_to' => $params[$w_time_to]
        ]);

        $join .= db_quote(" LEFT JOIN ?:orders as user_orders ON ?:users.user_id = user_orders.user_id AND (user_orders.timestamp >= ?i AND user_orders.timestamp <= ?i)", $params[$w_time_from], $params[$w_time_to]);
        if ($params['user_orders'] == 'without') {
            $condition['user_orders'] = db_quote(" AND user_orders.user_id IS NULL");
        }
        if ($params['user_orders'] == 'with') {
            $condition['user_orders'] = db_quote(" AND user_orders.user_id IS NOT NULL");
            if ($params['orders_period_amount']) {
                $subquery_cond = '';
                list($params['orders_period_time_from'], $params['orders_period_time_to']) = fn_create_periods([
                    'period' => $params['orders_period_period'],
                    'time_from' => $params['orders_period_time_from'],
                    'time_to' => $params['orders_period_time_to']
                ]);
                if ($params['orders_period_time_from']) {
                    $subquery_cond .= db_quote(' AND ?:orders.timestamp >= ?i ', $params['orders_period_time_from']);
                }
                if ($params['orders_period_time_to']) {
                    $subquery_cond .= db_quote(' AND ?:orders.timestamp <= ?i ', $params['orders_period_time_to']);
                }
                
                $operator = ($params['orders_period_operator'] == 'gt') ? '>' : '<';

                $subquery = db_quote("
                    SELECT DISTINCT
                        (?:users.user_id)
                    FROM
                        ?:users
                    LEFT JOIN ?:orders ON ?:orders.user_id = ?:users.user_id AND ?:orders.is_parent_order != 'Y'
                    
                    WHERE
                        1 ?p
                    GROUP BY
                        ?:users.user_id
                    HAVING count(?:orders.order_id) ?p ?i
                ", $subquery_cond, $operator, $params['orders_period_amount']);
                $condition['orders_amount'] = db_quote(" AND ?:users.user_id IN ($subquery)");
            }
        }
    }
    if (isset($params['registration_period']) && YesNo::toBool($params['registration_period'])) {
        list($registration_period_time_from, $registration_period_time_to) = fn_create_periods([
            'period' => $params['registration_period_period'],
            'time_from' => $params['registration_period_time_from'],
            'time_to' => $params['registration_period_time_to']
        ]);
        if ($registration_period_time_from) {
            $condition['registration_period_time_from'] = db_quote(' AND ?:users.timestamp >= ?i ', $registration_period_time_from);
        }
        if ($registration_period_time_to) {
            $condition['registration_period_time_to'] = db_quote(' AND ?:users.timestamp <= ?i ', $registration_period_time_to);
        }
    }

    $products_in_order = [];
    if (!empty($params['category_ids'])) {
        $products_in_order = db_get_fields(fn_get_products(['cid' => $params['category_ids'], 'subcats' => 'Y', 'get_query' => true]));
    }

    if (!empty($params['p_ids']) || !empty($params['category_ids'])) {
        list($params['ordered_products_time_from'], $params['ordered_products_time_to']) = fn_create_periods([
            'period' => $params['ordered_products_period'],
            'time_from' => $params['ordered_products_time_from'],
            'time_to' => $params['ordered_products_time_to']
        ]);
        $condition['order_period'] = db_quote(' AND ?:orders.timestamp > ?i AND ?:orders.timestamp < ?i', $params['ordered_products_time_from'], $params['ordered_products_time_to']);
    }

    if (!empty($params['ordered_type'])) {
        if ($params['ordered_type'] == 'IN') {

            if (!empty($products_in_order)) {
                $condition['ordered_products'] = db_quote(' AND ?:order_details.product_id IN (?n)', $products_in_order);
                if (strpos($join, 'LEFT JOIN ?:order_details') === false) {
                    $join .= db_quote(' LEFT JOIN ?:orders ON ?:orders.user_id = ?:users.user_id AND ?:orders.is_parent_order != ?s LEFT JOIN ?:order_details ON ?:order_details.order_id = ?:orders.order_id', YesNo::YES);
                }
            }
        } else {
            // not ordered products
            $subquery = db_quote("
            SELECT DISTINCT
                (?:users.user_id)
            FROM
                ?:users
            LEFT JOIN ?:orders ON ?:orders.user_id = ?:users.user_id AND ?:orders.is_parent_order != 'Y'
            LEFT JOIN ?:order_details ON ?:order_details.order_id = ?:orders.order_id
            WHERE
                1 AND ?:order_details.product_id IN (?a) ?p
            GROUP BY
                ?:users.user_id
            ", (!empty($products_in_order) ? $products_in_order : $params['p_ids']), $condition['order_period'] ?? '');

            $condition['not_ordered_products'] = db_quote(" AND ?:users.user_id NOT IN ($subquery)");
            unset($condition['order_product_id'], $condition['order_period']);
        }
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

function fn_maintenance_exim1c_update_product_usergroups_pre($product_data, &$ugroups) {
    $service_usergroups = Registry::get('addons.maintenance.service_usergroups');
    if (!empty($service_usergroups) && !empty($product_data['usergroup_ids'])) {
        if ($product_service_usergroups = array_intersect(explode(',', $product_data['usergroup_ids']), array_keys($service_usergroups))) {
            $ugroups = array_unique(array_merge($ugroups, $product_service_usergroups));
        }
    }
}

function fn_maintenance_get_product_filter_fields(&$filters) {
    $filters[ProductFilterProductFieldTypes::PRICE]['conditions'] = function($db_field, $join, $condition) {

        $join .= db_quote("
            LEFT JOIN ?:product_prices as prices_2 ON ?:product_prices.product_id = prices_2.product_id AND ?:product_prices.price > prices_2.price AND prices_2.lower_limit = 1 AND prices_2.usergroup_id IN (?n)",
            array_filter(Tygh::$app['session']['auth']['usergroup_ids'])
        );

        if (YesNo::toBool(Registry::get('addons.maintenance.ignore_price_for_usergroup_all'))) {
            $condition .= db_quote("
                AND ?:product_prices.lower_limit = 1 AND ?:product_prices.usergroup_id IN (?n) AND prices_2.price IS NULL",
                array_filter(Tygh::$app['session']['auth']['usergroup_ids'])
            );
        }

        if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id') && YesNo::toBool(Registry::get('addons.maintenance.ignore_price_for_usergroup_all'))) {
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

        if (YesNo::toBool(Registry::get('addons.maintenance.ignore_price_for_usergroup_all'))) {
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
    }

    if (!empty($params['exclude_cid'])) {
        if (!is_array($params['exclude_cid'])) $params['exclude_cid'] = explode(',', $params['exclude_cid']);
        $condition .= db_quote(" AND ?:categories.category_id NOT IN (?n)", $params['exclude_cid']);
    }
}

function fn_maintenance_load_products_extra_data(&$extra_fields, $products, $product_ids, &$params, $lang_code) {
    if (YesNo::toBool(Registry::get('addons.maintenance.ignore_price_for_usergroup_all'))) {
        // нет единого запроса, чтобы брались прайсовые цены и только если их нет брались базовые поэтому тут берем базовые а в fn_maintenance_load_products_extra_data_post берем поверх прайсовые если они есть
        if (
        in_array('prices', $params['extend'])
        && $params['sort_by'] != 'price'
        && !in_array('prices2', $params['extend'])
        ) {
            $extra_fields['?:product_prices']['condition'] = db_quote(
                ' AND ?:product_prices.lower_limit = 1 AND ?:product_prices.usergroup_id = ?i', USERGROUP_ALL);
        }

        $params['auth_usergroup_ids'] = array_filter(Tygh::$app['session']['auth']['usergroup_ids']);
    }
}

function fn_maintenance_load_products_extra_data_post(&$products, $product_ids, $params, $lang_code) {
    if (YesNo::toBool(Registry::get('addons.maintenance.ignore_price_for_usergroup_all'))) {
        if (!empty($params['auth_usergroup_ids'])) {
            $prices = db_get_hash_array("SELECT prices.product_id, IF(prices.percentage_discount = 0, min(prices.price), prices.price - (prices.price * prices.percentage_discount)/100) as price FROM ?:product_prices prices WHERE product_id IN (?a) AND lower_limit = ?i AND usergroup_id IN (?a) GROUP BY product_id", 'product_id', $product_ids, 1, $params['auth_usergroup_ids']);
            $products = fn_array_merge($products, $prices);
        }
    }
}

function fn_maintenance_get_product_data($product_id, $field_list, &$join, $auth, $lang_code, &$condition, &$price_usergroup) {
    if (YesNo::toBool(Registry::get('addons.maintenance.ignore_price_for_usergroup_all')) && SiteArea::isStorefront(AREA)) {
        $usergroup_ids = !empty($auth['usergroup_ids']) ? $auth['usergroup_ids'] : array();
        $price_usergroup = db_quote(' 
            AND CASE WHEN 
            (SELECT count(*) FROM ?:product_prices WHERE product_id = ?i AND cscart_product_prices.usergroup_id IN (?a) )
            THEN ?:product_prices.usergroup_id IN (?a) 
            ELSE ?:product_prices.usergroup_id = ?i END', $product_id, array_filter($usergroup_ids), array_filter($usergroup_ids), USERGROUP_ALL);
    }
}

function fn_maintenance_get_product_price($product_id, $amount, $auth, &$price, &$skip) {
    if (YesNo::toBool(Registry::get('addons.maintenance.ignore_price_for_usergroup_all'))) {
        $skip = true;
        $usergroup_ids = empty($usergroup_ids) ? $auth['usergroup_ids'] : $usergroup_ids;
        $usergroup_ids = array_filter($usergroup_ids);

        $price = db_get_field("
            SELECT min(IF(prices.percentage_discount = 0, prices.price, prices.price - (prices.price * prices.percentage_discount)/100)) as price 
            FROM ?:product_prices as prices 
            WHERE prices.product_id = ?i AND CASE WHEN 
                (SELECT count(*) FROM ?:product_prices WHERE product_id = ?i AND cscart_product_prices.usergroup_id IN (?n) )
                THEN prices.usergroup_id IN (?n) 
                ELSE prices.usergroup_id = ?i END ORDER BY lower_limit", $product_id, $product_id, $usergroup_ids, $usergroup_ids, USERGROUP_ALL);
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

function fn_maintenance_get_orders($params, $fields, $sortings, &$condition, &$join, &$group) {
    if (!empty($params['usergroup_id'])) {
        $auth = Tygh::$app['session']['auth'];
        list($users, ) = fn_get_users(array('usergroup_id' => $params['usergroup_id']), $auth);
        $condition .= db_quote(' AND ?:orders.user_id IN (?a)', array_column($users, 'user_id'));
    }

    if (isset($params['promotion_id']) && !empty($params['promotion_id'])) {
        $condition .= db_quote(" AND FIND_IN_SET(?i, promotion_ids)", $params['promotion_id']);
    }
}

function fn_maintenance_update_product_amount_before_tracking_checking($product_id, &$amount_delta, $product_options, $sign, $notify, $order_info) {
    if ((SiteArea::isAdmin(AREA) || in_array(Registry::get('runtime.controller'), ['ex_exim_1c', 'exim'])) && !YesNo::toBool(Registry::get('addons.maintenance.track_amount_in_backend'))) {
        $amount_delta = 0;
    }
}
