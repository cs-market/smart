<?php

use Tygh\Registry;
use Tygh\Models\Company;
use Tygh\Enum\ProductTracking;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_smart_distribution_get_orders($params, $fields, $sortings, &$condition, $join, $group) {
    $auth = $_SESSION['auth'];
    if (!empty($params['usergroup_id'])) {
        list($users, ) = fn_get_users(array('usergroup_id' => $params['usergroup_id']), $_SESSION['auth']);
        $condition .= db_quote(' AND ?:orders.user_id IN (?a)', fn_array_column($users, 'user_id'));
    }

    if (isset($params['user_ids']) && !empty($params['user_ids'])) {
        if (!is_array($params['user_ids'])) {
            $params['user_ids'] = explode(',', $params['user_ids']);
        }
        $condition .= db_quote(' AND ?:orders.user_id IN (?a)', $params['user_ids']);
    }
    if (fn_smart_distribution_is_manager($auth['user_id'])) {
        $params['managers'] = $auth['user_id'];
    }
    if (!empty($params['managers'])) {
        list($users, ) = fn_get_users(array('managers' => $params['managers']), $_SESSION['auth']);

        if ($users) {
            $condition .= db_quote(' AND ?:orders.user_id IN (?a)', fn_array_column($users, 'user_id'));
        } else {
            $condition .= db_quote(' AND 0');
        }
    }

    if (isset($params['promotion_id']) && !empty($params['promotion_id'])) {
        $condition .= db_quote(" AND FIND_IN_SET(?i, promotion_ids)", $params['promotion_id']);
    }

}

function fn_smart_distribution_get_order_info(&$order, $additional_data) {
    $auth = $_SESSION['auth'];
    if (AREA == 'A') {
        if (fn_smart_distribution_is_manager($auth['user_id'])) {
            $customer_ids = db_get_fields('SELECT customer_id FROM ?:vendors_customers WHERE vendor_manager = ?i', $auth['user_id']);
            if (!in_array($order['user_id'], $customer_ids)) {
                $order = false;
            }
        }
    }
    if (!($order['profile_id'])) {
        $user_profiles = fn_get_user_profiles($order['user_id']);
        if (count($user_profiles) == 1) {
            $profile = reset($user_profiles);
            $order['profile_id'] = $profile['profile_id'];
        } else {
            $order['profile_id'] = db_get_field('SELECT profile_id FROM ?:user_profiles WHERE user_id = ?i AND s_address = ?s', $order['user_id'], $order['s_address']);
            if (!$order['profile_id']) {

            }
        }
    }
    if (empty(array_filter($order['fields']))) {
        $prof_cond = (!empty($order['profile_id'])) ? db_quote("OR (object_id = ?i AND object_type = 'P')", $order['profile_id']) : '';
        $order['fields'] = db_get_hash_single_array("SELECT field_id, value FROM ?:profile_fields_data WHERE (object_id = ?i AND object_type = 'U') $prof_cond", array('field_id', 'value'), $user_id);
    }
    if (!empty($order['fields'])) {
        $fields = db_get_hash_single_array('SELECT field_id, field_name FROM ?:profile_fields WHERE field_id IN (?a)', array('field_id', 'field_name'), array_keys($order['fields']));
        foreach ($fields as $field_id => $field_name) {
            $order[$field_name] = $order['fields'][$field_id];
        }
    }

    // get_barcode for product
    if (defined('API')) {
        foreach ($order['products'] as &$product) {
            $features = fn_get_product_features_list($product, "A");
            if (!empty($features)) {
                foreach ($features as $feature) {
                    if (!empty($feature['feature_code'])) {
                        $product[$feature['feature_code']] = $feature['variant'];
                    }
                }
            }
        }
    }
}

function fn_smart_distribution_get_product_features_list_before_select(&$fields, $join, &$condition, $product, $display_on, $lang_code) {
    // to get a feature code in API request
    if (defined('API')) {
        $fields .=  ', f.feature_code';
    }
    // /to get a feature code in API request

    // [only vendor features]
    if (isset($product['product_id']) && $product['product_id']) {
        if (
            AREA == 'A'
            && fn_allowed_for('MULTIVENDOR')
        ) {
            $product_company_id = db_get_field("SELECT company_id FROM ?:products WHERE product_id = ?i", $product['product_id']);
            $condition .= db_quote(" AND (f.company_id = 0 OR f.company_id = ?i)", $product_company_id);
        }
    }
    // [/only vendor features]
}

function fn_smart_distribution_is_manager($user_id) {
    $val = db_get_field('SELECT is_manager FROM ?:users WHERE user_id = ?i', $user_id);
    return ($val == 'Y') ? true : false;
}

if (fn_allowed_for('MULTIVENDOR') && !function_exists('fn_ult_is_shared_product') ) {
    function fn_ult_is_shared_product($pid) {
        return 'N';
    }
}

function fn_smart_distribution_vendor_plan_before_save(&$obj, $result) {
    if (!empty($obj->usergroup_ids) && is_array($obj->usergroup_ids)) {
        $obj->usergroup_ids = implode(',', $obj->usergroup_ids);
    }
}

class UG_Company extends Company {
    public function getFields($params)
    {
        $fields = parent::getFields($params);
        $fields[] = 'p.usergroup_ids';
        return $fields;
    }

    public function gatherAdditionalItemsData(&$items, $params)
    {
        parent::gatherAdditionalItemsData($items, $params);
        foreach ($items as $key => $item) {
            $items[$key]['usergroup_ids'] = !empty($item['usergroup_ids']) ? explode(',', $item['usergroup_ids']) : array();
        }
    }
}

function fn_smart_distribution_get_usergroups($params, $lang_code, $field_list, $join, &$condition, $group_by, $order_by, $limit) {
    $company = UG_Company::model()->current();
    if ($company) {
        if ($company->usergroup_ids)
        $condition .= db_quote(' AND a.usergroup_id IN (?a)', $company->usergroup_ids);
    }

}

function fn_smart_distribution_post_get_usergroups(&$usergroups, $type, $lang_code) {
    $company = UG_Company::model()->current();

    if ($company) {
        if ($company->usergroup_ids) {
            $ug_uds = $company->usergroup_ids;
            $usergroups = array_filter($usergroups, function($k) use ($ug_uds) {
                return in_array($k, $ug_uds);
            }, ARRAY_FILTER_USE_KEY);

        }
    }
}

function fn_smart_distribution_get_simple_usergroups_pre(&$where) {
    $company = UG_Company::model()->current();
    if ($company) {
        if ($company->usergroup_ids)
        $where .= db_quote(' AND a.usergroup_id IN (?a)', $company->usergroup_ids);
    }
}

function fn_smart_distribution_get_managers($params = array()) {
    $condition = '';
    if (Registry::get('runtime.company_id') || !empty($params['company_id'])) {
        $company_id = (Registry::get('runtime.company_id')) ? Registry::get('runtime.company_id') : $params['company_id'];
            $condition .= db_quote(" AND u.company_id = ?i", $company_id);
    }
    if (isset($params['user_id'])) {
        $condition .= db_quote(" AND vc.customer_id = ?i", $params['user_id']);
    }

    $managers = db_get_hash_array("SELECT DISTINCT(vc.vendor_manager) as user_id, u.email, IF(CONCAT(firstname, lastname) = '', email, CONCAT(firstname, ' ', lastname)) AS name, u.company_id FROM ?:vendors_customers AS vc LEFT JOIN ?:users AS u ON u.user_id = vc.vendor_manager WHERE 1 $condition", 'user_id');

    return $managers;
}

function fn_smart_distribution_get_users_pre(&$params, $auth, $items_per_page, $custom_view) {
    if (Registry::get('runtime.company_id')) {
        $params['exclude_user_types'] = array('V');
    }
}
function fn_smart_distribution_get_users(&$params, &$fields, $sortings, &$condition, &$join, $auth) {
    $fields[] = 'last_update';
    if (!empty($params['managers'])) {
        if (!is_array($params['managers'])) {
            $managers = explode(',', $params['managers']);
        }
        $join .= db_quote(' LEFT JOIN ?:vendors_customers ON ?:vendors_customers.customer_id = ?:users.user_id');
        $condition['vendor_manager'] = db_quote(' AND ?:vendors_customers.vendor_manager in (?a) ', $managers);
    }
    if (Registry::get('runtime.company_id')) {
        $params['company_id'] = Registry::get('runtime.company_id');
    }
    if (isset($condition['users_company_id'])) {
        unset($condition['users_company_id']);
    }
    if (isset($condition['company_id'])) {
        unset($condition['company_id']);
    }
    if (isset($params['company_id']) && !empty($params['company_id'])) {
        // temporary condition
        if (!(in_array(Registry::get('runtime.controller'), ['sd_exim_1c', 'commerceml']) && in_array($params['company_id'], [41,46])))
        $condition['sd_condition'] = ' AND (' . fn_get_company_condition('?:users.company_id', false, $params['company_id'], false, true) . db_quote(" OR ?:users.user_id IN (?n)" . ' )', fn_get_company_customers_ids($params['company_id']));
    }
    // for search in profile fields
    if (!empty($params['search_query'])) {
        $search_string = '%' . trim($params['search_query']) . '%';
        $condition['name'] = db_quote(' AND ?:users.firstname LIKE ?l OR ?:users.lastname LIKE ?l OR ?:profile_fields_data.value LIKE ?l', $search_string, $search_string, $search_string);
        $join .= db_quote(' LEFT JOIN ?:profile_fields_data ON ?:profile_fields_data.object_id = ?:user_profiles.profile_id AND ?:profile_fields_data.object_type = ?s', 'P');
    }

    $without_order_prefix = 'without_order_';
    if (!empty($params['wo_orders']) && $params['wo_orders'] == 'Y') {
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

        $join .= db_quote(" LEFT JOIN ?:orders as without_order ON ?:users.user_id = without_order.user_id AND (without_order.timestamp >= ?i AND without_order.timestamp <= ?i)", $params[$w_time_from], $params[$w_time_to]);
        $condition['without_order'] = db_quote(" AND without_order.user_id IS NULL");
    }
}

function fn_smart_distribution_get_users_post(&$users, $params, $auth) {
    if (defined('API') && isset($params['user_id']) && is_numeric($params['user_id'])) {
        // requested info about single user via api
        $users = array(fn_get_user_info($params['user_id']));
    }
}

function fn_smart_distribution_get_user_info_before(&$condition, $user_id, $user_fields, $join) {
    if (trim($condition) && Registry::get('runtime.company_id')) {
        // reset condition for vendor's user visit
        if ($user_id != Tygh::$app['session']['auth']['user_id']
            && (!fn_allowed_for('ULTIMATE')
                || Registry::ifGet('settings.Stores.share_users', 'N') === 'N'
            )
        ) {
            $condition = fn_get_company_condition('?:users.company_id');

            $condition = db_quote("(user_type IN (?a) $condition)", array('C', 'V'));
            $company_customers = db_get_fields("SELECT user_id FROM ?:orders WHERE company_id = ?i", Registry::get('runtime.company_id'));
            if ($company_customers) {
                $condition = db_quote("(user_id IN (?n) OR $condition)", $company_customers);
            }
            $condition = " AND $condition ";

        }
    }
}

function fn_smart_distribution_get_user_info($user_id, $get_profile, $profile_id, &$user_data) {
    // get managers for single user
    $user_data['managers'] = fn_smart_distribution_get_managers(array('user_id' => $user_id));
}

function fn_smart_distribution_update_user_pre($user_id, &$user_data, $auth, $ship_to_another, $notify_user) {
    $user_data['last_update'] = time();
}

function fn_smart_distribution_update_user_profile_pre($user_id, $user_data, $action) {
    if ($user_data['user_id'] && isset($user_data['managers']) && $user_data['user_type'] == 'C') {
        $managers = fn_smart_distribution_get_managers(array('user_id' => $user_data['user_id']));

        if ($managers) db_query("DELETE FROM ?:vendors_customers WHERE customer_id = ?i AND vendor_manager IN (?a)", $user_data['user_id'], array_keys($managers));

        $udata = array();
        if (!empty($user_data['managers'])) {
            if (!is_array($user_data['managers'])) {
                $managers = explode(',', $user_data['managers']);
            } else {
                $managers = array_column($user_data['managers'], 'user_id');
            }

            // API OPERATES BY EMAILS!
            if (defined('API')) {
                $managers = db_get_fields(
                "SELECT user_id FROM ?:users WHERE email IN (?a)",
                array_map('trim', $user_data['managers'])
                );
            }
            foreach ($managers as $m_id) {
                if ($m_id) $udata[] = array('vendor_manager' => $m_id, 'customer_id' => $user_data['user_id']);
            }

            if (!empty($udata)) db_query('INSERT INTO ?:vendors_customers ?m', $udata);
        }
    }
}

function fn_smart_distribution_update_profile($action, $user_data, $current_user_data) {
    if ($action == 'add' && AREA == 'C' && !empty($user_data['usergroup_ids'])) {
        $ids = explode(',', $user_data['usergroup_ids']);
        foreach ($ids as $ug_id) {
            fn_change_usergroup_status('A', $user_data['user_id'], $ug_id);
        }
    }
}

function fn_smart_distribution_gather_additional_product_data_post(&$product, $auth, $params) {
    // for discount label in mobile application
    if (isset($product['discount'])) {
        if (!( (float) $product['list_price'])) {
            $product['list_price'] = $product['base_price'];
        }
        if (!isset($product['list_discount'])) {
            $product['list_discount'] = $product['discount'];
            $product['list_discount_prc'] = $product['discount_prc'];
        }
    }
    // for in_stock | out_of_stock in mobile application
    if ($product['tracking'] == 'D' && $product['amount'] < 0 ) {
        $product['amount'] = abs($product['amount']);
    }
}

function fn_smart_distribution_sales_reports_table_condition(&$table_condition, $k, $v, &$table) {
    if (isset($_REQUEST['dynamic_conditions'])) {
        $dynamic_conditions = $_REQUEST['dynamic_conditions'];
        foreach ($dynamic_conditions as $type => $condition) {

            if ($type == 'category') {
                $categories = explode(',', $condition);
                $condition = array_combine($categories, $categories);
            }
            if ($type == 'user') {
                $users = explode(',', $condition);
                $condition = array_combine($users, $users);
            }
            if ($type == 'managers') {
                $type = 'user';
                list($users, ) = fn_get_users(array('managers' => $condition), $_SESSION['auth']);
                $users = fn_array_column($users, 'user_id');
                $condition = array_combine($users, $users);
            }
            if ($type == 'usergroup_id') {
                $type = 'user';
                list($users, ) = fn_get_users(array('usergroup_id' => $condition), $_SESSION['auth']);
                $users = fn_array_column($users, 'user_id');
                $condition = array_combine($users, $users);
            }
            $table_condition[$type] = $condition;
        }
        if (isset($dynamic_conditions['display'])) {
            $table['display'] = $dynamic_conditions['display'];
        }
    }
}


function fn_smart_distribution_sales_reports_change_table(&$value, $key) {
    if (isset($_REQUEST['dynamic_conditions'])) {
        $dynamic_conditions = $_REQUEST['dynamic_conditions'];
        if (isset($dynamic_conditions['interval_id'])) {
            $value['interval_id'] = $dynamic_conditions['interval_id'];
        }
        if (isset($dynamic_conditions['display'])) {
            $value['display'] = $dynamic_conditions['display'];
        }
    }
}


function fn_array_group(array $array, $key)
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
            $grouped[$key] = call_user_func_array('fn_array_group', $params);
        }
    }
    return $grouped;
}

function fn_smart_distribution_get_default_usergroups(&$default_usergroups, $lang_code) {
    if (Registry::get('runtime.company_id')) {
        $default_usergroups = array();
    }
}

function fn_smart_distribution_api_handle_request($_this, $authorized) {
    //if ($_SESSION['auth']['user_id'] == '2425' && $_this->getRequest()->getMethod() == 'PUT')
    //fn_write_r(date('H:m:s d/m/Y') . ' ' . $_this->getRequest()->getResource() . ' ' . $_this->getRequest()->getMethod(), $_this->getRequest()->getData());
}

function fn_smart_distribution_api_send_response($_this, $response, $authorized) {
    //fn_write_r($response->body);
}

function fn_write_r() {
  static $count = 0;
  $args = func_get_args();
  $fp = fopen('api_requests.html', 'w+');
  if (!empty($args)) {
    fwrite($fp, '<ol style="font-family: Courier; font-size: 12px; border: 1px solid #dedede; background-color: #efefef; float: left; padding-right: 20px;">');
    foreach ($args as $k => $v) {
      $v = htmlspecialchars(print_r($v, true));
      if ($v == '') { $v = ' '; }
      fwrite($fp, '<li><pre>' . $v . "\n" . '</pre></li>');
    }
    fwrite($fp, '</ol><div style="clear:left;"></div>');
  }
  $count++;
}


// temp function
function fn_import_bering_usergroups() {
    $file = 'users.csv';
    $ugroups = fn_exim_get_csv(array(), $file );
    foreach ($ugroups as &$group) {
        $group = array_merge($group, array(
            'type' => "C",
            'status' => 'A'
        ));
        $group['usergroup_id'] = fn_update_usergroup($group);
    }
    $new_groups = implode(',', fn_array_column($ugroups, 'usergroup_id'));

    $res = db_query("UPDATE ?:vendor_plans SET `usergroup_ids`= IF(usergroup_ids = '0', ?s, CONCAT(usergroup_ids, ?s)) WHERE plan_id = 16;", $new_groups, ',' . $new_groups);


    $categories = db_get_field('select categories from ?:vendor_plans WHERE plan_id = ?i', 16);
    $categories = explode(',', $categories);
    $res1 = db_query("UPDATE ?:categories SET `usergroup_ids`= IF(usergroup_ids = '0', ?s, CONCAT(usergroup_ids, ?s)) WHERE category_id in (?a)", $new_groups, ',' . $new_groups,  $categories);
    $res2 = db_query("UPDATE ?:products SET `usergroup_ids`= IF(usergroup_ids = '0', ?s, CONCAT(usergroup_ids, ?s)) WHERE company_id = ?i", $new_groups, ',' . $new_groups,  29);
    fn_print_die($res, $res1, $res2);
}

// $obj = db_get_array('select profile_id, object_id, value FROM ?:user_profiles LEFT JOIN ?:profile_fields_data ON user_id = object_id WHERE ?:profile_fields_data.FIELD_ID = ?i and ?:profile_fields_data.value != ?s AND object_type = ?s', 36, '', 'U');

// $obj1 = db_get_array('select object_id, object_type, value from ?:profile_fields_data WHERE FIELD_ID = ?i and value = ?s AND object_id IN (?a)  AND object_type = ?s', 39, '', array_column($obj, 'profile_id'), 'P');
// fn_print_die($obj1);

// fn_print_die($obj1);
// $obj2 = db_get_array('select object_id, value, object_type from ?:profile_fields_data WHERE FIELD_ID = ?i and value != ?s AND object_id IN (?a) AND object_type = ?s', 36, '', array_column($obj, 'object_id'), 'U');
// fn_print_die($obj2);
// fn_print_die($obj1);
function fn_smart_distribution_update_category_pre(&$category_data, $category_id, $lang_code) {
    if (isset($_REQUEST['preset_id']) && !$category_id) {
        list($presets) = fn_get_import_presets(array(
            'preset_id' => $_REQUEST['preset_id'],
        ));
        $preset = reset($presets);
        if ($preset['company_id']) {
            $usergroup_ids = db_get_field("SELECT usergroup_ids FROM ?:vendor_plans LEFT JOIN ?:companies ON ?:companies.plan_id = ?:vendor_plans.plan_id WHERE company_id = ?i", $preset['company_id']);
            $category_data['usergroup_ids'] = explode(',',$usergroup_ids);
        }
        $category_data['add_category_to_vendor_plan'] = $preset['company_id'];

    }
}

function fn_smart_distribution_update_category_post($category_data, $category_id, $lang_code) {
    if (isset($category_data['add_category_to_vendor_plan'])) {
        $plan_id = db_get_field('SELECT plan_id FROM ?:companies WHERE company_id = ?i', $category_data['add_category_to_vendor_plan']);
        if ($plan_id) {
            $res1 = db_query("UPDATE ?:vendor_plans SET `categories`= IF(categories = '', ?s, CONCAT(categories, ?s)) WHERE plan_id = ?i", $category_id, ',' . $category_id,  $plan_id);
        }
    }
}

function fn_smart_distribution_set_product_categories_exist($category_id) {
    if (isset($_REQUEST['preset_id'])) {
        list($presets) = fn_get_import_presets(array(
            'preset_id' => $_REQUEST['preset_id'],
        ));
        $preset = reset($presets);
        if ($preset['company_id']) {
            $usergroups = db_get_field("SELECT usergroup_ids FROM ?:vendor_plans LEFT JOIN ?:companies ON ?:companies.plan_id = ?:vendor_plans.plan_id WHERE company_id = ?i", $preset['company_id']);
            if (!empty($usergroups)) {
                $c_groups = array();
                if ($category_id) {
                    $c_groups = db_get_field('SELECT usergroup_ids FROM ?:categories WHERE category_id = ?i', $category_id);
                }
                $usergroups = explode(',',$usergroups);
                $c_groups = explode(',',$c_groups);
                $usergroup_ids = implode(',',array_unique(array_merge($usergroups, $c_groups)));
                db_query('UPDATE ?:categories SET `usergroup_ids` = ?s WHERE category_id = ?i', $usergroup_ids, $category_id);
            }
        }
    }
}

function fn_smart_distribution_pre_add_to_cart(&$product_data, &$cart, $auth, $update) {
    // specual modification for dmitry plotvinov
    if ((!empty($_SESSION['auth']['company_id'])) && defined('API')) {
        $_product_data = array();
        foreach ($product_data as $key => $product) {
            if (!fn_check_company_id('products', 'product_id', $key, $_SESSION['auth']['company_id'])) {
                $product_id = db_get_field('SELECT product_id FROM ?:products WHERE company_id = ?i AND product_code = ?s', $_SESSION['auth']['company_id'], $key);
                if ($product_id) $_product_data[$product_id] = $product;
            } else {
                $_product_data[$key] = $product;
            }
        }
        $product_data = $_product_data;
    }

    // disable popup notification
    $cart['skip_notification'] = true;
}

// wishlist in mobile application should have qty_step
function fn_smart_distribution_add_to_cart(&$cart, $product_id, $_id) {
    $cart['products'][$_id]['qty_step'] = db_get_field('SELECT qty_step FROM ?:products WHERE product_id = ?i', $product_id);
}

function fn_smart_distribution_get_profile_fields($location, $select, &$condition) {
    if (AREA == 'C' && in_array(Registry::get('runtime.controller'), array('checkout', 'profiles'))) {
        $stop_fields = array(
            //'s_address',
            's_lastname'
        );
        if ($_SESSION['auth']['company_id'] != '12') {
            $stop_fields[] = 's_address_2';
        }
        if (Registry::get('runtime.mode') == 'add') {
            $stop_fields[] = 'company';
            $stop_fields[] = 'fax';
        } else {
            $stop_fields[] = 'b_client_code';
            $stop_fields[] = 's_client_code';
            $stop_fields[] = 'client_city';
        }
        $condition .= db_quote(" AND field_name NOT IN (?a)", $stop_fields);
    }
}

function fn_smart_distribution_vendor_communication_add_thread_message_post( $thread_full_data, $result) {
    if ($thread_full_data['last_message_user_type'] != 'A') {
        $managers = fn_smart_distribution_get_managers(array('user_id' => $thread_full_data['last_message_user_id']));
        if (!empty($managers)) {
            $vendor_email = fn_array_column($managers, 'email');
            if (!empty($thread_full_data['last_message_user_id'])) {
                $message_from = fn_vendor_communication_get_user_name($thread_full_data['last_message_user_id']);
            }

            $email_data = array(
                'area' => 'A',
                'email' => $vendor_email,
                'email_data' => array(
                    'thread_url' => fn_url("vendor_communication.view&thread_id={$thread_data['thread_id']}", 'V'),
                    'message_from' => !empty($message_from) ? $message_from : fn_get_company_name($thread_data['company_id']),
                ),
                'template_code' => 'vendor_communication.notify_admin',
            );

            $result = fn_vendor_communication_send_email_notification($email_data);
        }
    }
}

function fn_sd_add_product_to_wishlist($product_data, &$wishlist, &$auth)
{
    if (is_callable('fn_add_product_to_wishlist')) {
        return fn_add_product_to_wishlist($product_data, $wishlist, $auth);
    } else {
        // Check if products have cusom images
        list($product_data, $wishlist) = fn_add_product_options_files($product_data, $wishlist, $auth, false, 'wishlist');

        fn_set_hook('pre_add_to_wishlist', $product_data, $wishlist, $auth);

        if (!empty($product_data) && is_array($product_data)) {
            $wishlist_ids = array();
            foreach ($product_data as $product_id => $data) {
                if (empty($data['amount'])) {
                    $data['amount'] = 1;
                }
                if (!empty($data['product_id'])) {
                    $product_id = $data['product_id'];
                }

                if (empty($data['extra'])) {
                    $data['extra'] = array();
                }

                // Add one product
                if (!isset($data['product_options'])) {
                    $data['product_options'] = fn_get_default_product_options($product_id);
                }

                // Generate wishlist id
                $data['extra']['product_options'] = $data['product_options'];
                $_id = fn_generate_cart_id($product_id, $data['extra']);

                $_data = db_get_row('SELECT is_edp, options_type, tracking FROM ?:products WHERE product_id = ?i', $product_id);
                $data['is_edp'] = $_data['is_edp'];
                $data['options_type'] = $_data['options_type'];
                $data['tracking'] = $_data['tracking'];

                // Check the sequential options
                if (!empty($data['tracking']) && $data['tracking'] == ProductTracking::TRACK_WITH_OPTIONS && $data['options_type'] == 'S') {
                    $inventory_options = db_get_fields("SELECT a.option_id FROM ?:product_options as a LEFT JOIN ?:product_global_option_links as c ON c.option_id = a.option_id WHERE (a.product_id = ?i OR c.product_id = ?i) AND a.status = 'A' AND a.inventory = 'Y'", $product_id, $product_id);

                    $sequential_completed = true;
                    if (!empty($inventory_options)) {
                        foreach ($inventory_options as $option_id) {
                            if (!isset($data['product_options'][$option_id]) || empty($data['product_options'][$option_id])) {
                                $sequential_completed = false;
                                break;
                            }
                        }
                    }

                    if (!$sequential_completed) {
                        fn_set_notification('E', __('error'), __('select_all_product_options'));
                        // Even if customer tried to add the product from the catalog page, we will redirect he/she to the detailed product page to give an ability to complete a purchase
                        $redirect_url = fn_url('products.view?product_id=' . $product_id . '&combination=' . fn_get_options_combination($data['product_options']));
                        $_REQUEST['redirect_url'] = $redirect_url; //FIXME: Very very very BAD style to use the global variables in the functions!!!

                        return false;
                    }
                }

                $wishlist_ids[] = $_id;
                $wishlist['products'][$_id]['product_id'] = $product_id;
                $wishlist['products'][$_id]['product_options'] = $data['product_options'];
                $wishlist['products'][$_id]['extra'] = $data['extra'];
                $wishlist['products'][$_id]['amount'] = $data['amount'];
            }

            return $wishlist_ids;
        } else {
            return false;
        }
    }
}
function fn_smart_distribution_pre_update_order(&$cart, $order_id) {
    $wishlist = & Tygh::$app['session']['wishlist'];
    $auth = & Tygh::$app['session']['auth'];
    $product_data = array();
    foreach ($cart['products'] as $product) {
        $product_data[$product['product_id']] = array(
            'product_id' => $product['product_id'],
            'amount' => $product['amount']
        );
    }
    $product_ids = fn_sd_add_product_to_wishlist($product_data, $wishlist, $auth);

    //  original products
    if (isset($cart['original_products'])) {
        $cart['products'] = fn_diff_original_products($cart['original_products'], $cart['products']);
    }
}

// for exim to escape /n at the end
function fn_smart_distribution_get_company_id_by_name($company_name, &$condition) {
    $condition = str_replace('\\n', '', $condition);
}

// fix qty_discounts update by API for product wo price
function fn_smart_distribution_update_product_pre(&$product_data, $product_id, $lang_code, $can_update) {
    if (!isset($product_data['price'])) {
        $price = db_get_field('SELECT price FROM ?:product_prices WHERE product_id = ?i AND usergroup_id = ?i AND lower_limit = ?i', $product_id, 0, 1);
        $qty_price = 0;
        if (isset($product_data['prices'])) {
            $qty_price = max(array_column($product_data['prices'], 'price'));
        }
        $product_data['price'] = ($qty_price > $price) ? $qty_price : $price;
    }

    // limit vendors usergroups
    $cid = ($product_data['company_id']) ? $product_data['company_id'] : db_get_field('SELECT company_id FROM ?:products WHERE product_id = ?i', $product_id);

    if ($cid) {
        $company = UG_Company::model()->find($cid);
        if ($company && !empty($company->usergroup_ids)) {
            if (!$product_id && !$product_data['usergroup_ids']) {
                $product_data['usergroup_ids'] = $company->usergroup_ids;
            }

            if ($product_data['usergroup_ids']) {
                $allowed_ug = array_merge($company->usergroup_ids, [0,1,2]);
                $product_data['usergroup_ids'] = array_intersect($product_data['usergroup_ids'], $allowed_ug);
            }
        }
    }
}

// for update order products content by back sync from 1c
function fn_smart_distribution_shippings_get_shippings_list_conditions($group, $shippings, $fields, $join, &$condition, $order_by) {
    if (Registry::get('runtime.controller') == 'sd_exim_1c') {
        $remove = " AND (" . fn_find_array_in_set(\Tygh::$app['session']['auth']['usergroup_ids'], '?:shippings.usergroup_ids', true) . ")";
        $condition = str_replace($remove, '', $condition);
    }
}

function fn_smart_distribution_place_order($order_id, $action, $order_status, $cart, $auth) {
    $order_info = fn_get_order_info($order_id);
    $field = ($action == 'save') ? 'notify_manager_order_update' : 'notify_manager_order_create';
    if (db_get_field("SELECT $field FROM ?:companies WHERE company_id = ?i", $order_info['company_id']) == 'Y') {
        $mailer = Tygh::$app['mailer'];
        list($shipments) = fn_get_shipments_info(array('order_id' => $order_info['order_id'], 'advanced_info' => true));
        $use_shipments = !fn_one_full_shipped($shipments);
        $payment_id = !empty($order_info['payment_method']['payment_id']) ? $order_info['payment_method']['payment_id'] : 0;
        $company_lang_code = fn_get_company_language($order_info['company_id']);
        $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true, false, ($order_info['lang_code'] ? $order_info['lang_code'] : CART_LANGUAGE), $order_info['company_id']);
        $status_settings = $order_statuses[$order_info['status']]['params'];

        $managers = fn_smart_distribution_get_managers(array('user_id' => $order_info['user_id'], 'company_id' => $order_info['company_id']));
        $email_template_name = 'order_notification.' . (($action == 'save') ? 'y' : 'o');

        $order_status = ($order_status == 'N') ? 'O' : $order_status;
        $mailer->send(array(
            'to' => array_column($managers, 'email'),
            'from' => 'default_company_orders_department',
            'reply_to' => $order_info['email'],
            'data' => array(
                'order_info' => $order_info,
                'shipments' => $shipments,
                'use_shipments' => $use_shipments,
                'order_status' => fn_get_status_data($order_status, STATUSES_ORDER, $order_info['order_id'], $company_lang_code),
                'payment_method' => fn_get_payment_data($payment_id, $order_info['order_id'], $company_lang_code),
                'status_settings' => $status_settings,
                'profile_fields' => fn_get_profile_fields('I', '', $company_lang_code),
                'secondary_currency' => $secondary_currency
            ),
            'template_code' => $email_template_name,
            'tpl' => 'orders/order_notification.tpl', // this parameter is obsolete and is used for back compatibility
            'company_id' => $order_info['company_id'],
        ), 'A', $company_lang_code);
    }
}

function fn_smart_distribution_get_notification_rules(&$force_notification, $params, $disable_notification) {
    $force_notification = array();
    if ($disable_notification) {
        $force_notification = array('C' => false, 'A' => false, 'V' => false);
    } else {
        if (!empty($params['notify_user']) || $params === true) {
            $force_notification['C'] = true;
        } else {
            if (AREA == 'A' || Registry::get('runtime.controller') == 'sd_exim_1c') {
                $force_notification['C'] = false;
            }
        }
        if (!empty($params['notify_department']) || $params === true) {
            $force_notification['A'] = true;
        } else {
            if (AREA == 'A' || Registry::get('runtime.controller') == 'sd_exim_1c') {
                $force_notification['A'] = false;
            }
        }
        if (!empty($params['notify_vendor']) || $params === true) {
            $force_notification['V'] = true;
        } else {
            if (AREA == 'A' || Registry::get('runtime.controller') == 'sd_exim_1c') {
                $force_notification['V'] = false;
            }
        }
    }
}

function fn_render_xml_from_array($data, $parent_tag = '', $parent_attributes = '') {
    $rendered = '';
    if (is_array($data)) {
        foreach ($data as $tag => $value) {
            if (is_numeric($tag)) {
                $is_numeric = true;
                $tag = $parent_tag;
                $attributes = $parent_attributes;
            }
            if (!is_array($value)) {
                $rendered .= fn_render_xml_from_array($value, $tag, $attributes);
            } else {
                if (isset($value['@attributes'])) {
                    $attributes = '';
                    foreach ($value['@attributes'] as $attr_name => $attr) {
                        $attributes .= ' ' . $attr_name . '="' . $attr . '"';
                    }
                    unset($value['@attributes']);
                    if (empty($value)) {
                        $value = '';
                    }
                }

                $tag_content = fn_render_xml_from_array($value, $tag, $attributes);
                if (!empty($value) && !is_numeric(key($value))) {
                    $rendered .= fn_render_xml_from_array($tag_content, $tag, $attributes);
                } else {
                    $rendered .= $tag_content;
                }
            }
        }
    } else {
        if (trim($data) != '') {
            $rendered .= "<$parent_tag$parent_attributes>$data</$parent_tag>";
        } else {
            $rendered .= "<$parent_tag$parent_attributes/>";
        }
    }

    return $rendered;
}

// remove keys for sorting in mobile application
function fn_smart_distribution_get_product_features_list_post(&$features_list, $product, $display_on, $lang_code) {
    if (defined('API')) {
        $features_list = array_values($features_list);
    }
}

function fn_smart_distribution_form_cart($order_info, &$cart, $auth) {
    $cart['original_products'] = $order_info['products'];
}

function fn_smart_distribution_calculate_cart_post($cart, $auth, $calculate_shipping, $calculate_taxes, $options_style, $apply_cart_promotions, &$cart_products, $product_groups) {
    if (isset($cart['original_products'])) {
      $cart_products = fn_diff_original_products($cart['original_products'], $cart['products']);
    }
}

function fn_smart_distribution_update_cart_by_data_post(&$cart, $new_cart_data, $auth) {
    if (isset($cart['original_products']) && AREA == 'A') {
        foreach($new_cart_data['cart_products'] as $key => $product) {
            if (isset($cart['original_products'][$key])) {
                if ($product['amount'] == 0) {
                    $cart['products'][$key]['amount'] = 0;
                } else {
                    $cart['original_products'][$key]['change_amount'] = $product['amount'];
                }
            }
        }
    }
}

function fn_diff_original_products($original_products, $products)
{
    $diff_product = array_diff_key($original_products, $products);

    if ($diff_product) {
        array_walk($diff_product, function(&$p) {
            $p['amount'] = $p['change_amount'] ?? 0;
        });

        $products = fn_array_merge($diff_product, $products);
    }

    return $products;
}

function fn_smart_distribution_get_products($params, &$fields, $sortings, &$condition, &$join, $sorting, $group_by, $lang_code, $having) {
    if (Tygh::$app['session']['auth']['area'] == 'A' && AREA == 'C') {
        $condition = explode(' AND ', $condition);
      foreach($condition as $id => $cond) {
        if (strpos($cond, 'usergroup_ids') !== false
          || strpos($cond, 'usergroup_id') !== false
        ) {
          unset($condition[$id]);
        }
      }
      $condition = implode(' AND ', $condition);

        $join = explode(' JOIN ', $join);
        foreach($join as &$j) {
            $j = explode(' AND ', $j);
          foreach($j as $id => $cond) {
            if (strpos($cond, 'usergroup_ids') !== false
              || strpos($cond, 'usergroup_id') !== false
            ) {
              unset($j[$id]);
            }
          }
          $j = implode(' AND ', $j);
        }
        unset($j);
        $join = implode(' JOIN ', $join);
    }
    if (AREA == 'A') {
        $fields['timestamp'] = "products.timestamp";
        if (isset($params['product_code']) && !empty($params['product_code'])) {
            $condition .= db_quote(" AND products.product_code LIKE ?l", trim($params['product_code']));
        }
    }
}

function fn_smart_distribution_get_categories(&$params, $join, &$condition, $fields, $group_by, $sortings, $lang_code) {
    if (Tygh::$app['session']['auth']['area'] == 'A') {
        $condition = explode(' AND ', $condition);
        foreach($condition as $id => $cond) {
            if (strpos($cond, 'usergroup_ids') !== false
            || strpos($cond, 'usergroup_id') !== false
            ) {
                unset($condition[$id]);
            }
        }
        $condition = implode(' AND ', $condition);
    }
    if ($params['is_search'] == 'Y') {
        $params['group_by_level'] = false;
        $params['simple'] = false;
    }
}

function fn_smart_distribution_get_product_data($product_id, $field_list, &$join, $auth, $lang_code, $condition, &$price_usergroup) {
    if (Tygh::$app['session']['auth']['area'] == 'A') {
        $join = explode(' JOIN ', $join);
        foreach($join as &$j) {
            $j = explode(' AND ', $j);
          foreach($j as $id => $cond) {
            if (strpos($cond, 'usergroup_ids') !== false
              || strpos($cond, 'usergroup_id') !== false
            ) {
              unset($j[$id]);
            }
          }
          $j = implode(' AND ', $j);
        }
        unset($j);
        $join = implode(' JOIN ', $join);
    }

    $price_usergroup = db_quote(" AND ?:product_prices.usergroup_id = ?i", USERGROUP_ALL);
}

function fn_smart_distribution_get_usergroups_price($product_id, $usergroup_ids = array()) {
    $usergroup_ids = empty($usergroup_ids) ? Tygh::$app['session']['auth']['usergroup_ids'] : $usergroup_ids;
    $usergroup_ids = array_filter($usergroup_ids);
    return db_get_field("SELECT IF(prices.percentage_discount = 0, prices.price, prices.price - (prices.price * prices.percentage_discount)/100) as price FROM ?:product_prices prices WHERE prices.product_id = ?i AND prices.usergroup_id IN (?n) ORDER BY lower_limit", $product_id, $usergroup_ids);
}

function fn_smart_distribution_get_product_data_post(&$product_data, $auth, $preview, $lang_code) {
    if (AREA == 'C') {
        $product_data['usergroup_price'] = fn_smart_distribution_get_usergroups_price($product_data['product_id'], $auth['usergroup_ids']);
        if (!empty($product_data['usergroup_price'])) {
            $product_data['price'] = $product_data['usergroup_price'];
        }
    }
    // buy together for mobile application
    if (defined('API')) {
        $p['product_id'] = $product_data['product_id'];
        $p['status'] = 'A';
        $p['full_info'] = true;
        $p['date'] = true;
        $product_data['buy_together'] = fn_buy_together_get_chains($p, $auth);
    }
}

function fn_smart_distribution_get_product_price_post($product_id, $amount, $auth, &$price) {
    $usergroup_condition = db_quote(" AND ?:product_prices.usergroup_id = ?i", USERGROUP_ALL);

    $price = db_get_field(
        "SELECT MIN(IF(?:product_prices.percentage_discount = 0, ?:product_prices.price, "
            . "?:product_prices.price - (?:product_prices.price * ?:product_prices.percentage_discount)/100)) as price "
        . "FROM ?:product_prices "
        . "WHERE lower_limit <=?i AND ?:product_prices.product_id = ?i ?p "
        . "ORDER BY lower_limit DESC LIMIT 1",
        $amount, $product_id, $usergroup_condition
    );
    $usergroup_price = fn_smart_distribution_get_usergroups_price($product_id, $auth['usergroup_ids']);
    if (!empty($usergroup_price)) {
        $price = $usergroup_price;
    }
}

function fn_smart_distribution_load_products_extra_data(&$extra_fields, $products, $product_ids, $params, $lang_code) {
    if (
    in_array('prices', $params['extend'])
    && $params['sort_by'] != 'price'
    && !in_array('prices2', $params['extend'])
    ) {
        $extra_fields['?:product_prices']['condition'] = db_quote(
            ' AND ?:product_prices.lower_limit = 1 AND ?:product_prices.usergroup_id = ?i', USERGROUP_ALL);
    }
}

function fn_smart_distribution_load_products_extra_data_post(&$products, $product_ids, $params, $lang_code) {
    $usergroup_ids = Tygh::$app['session']['auth']['usergroup_ids'];
    $usergroup_ids = array_filter($usergroup_ids);
    $prices = db_get_hash_array("SELECT prices.product_id, IF(prices.percentage_discount = 0, prices.price, prices.price - (prices.price * prices.percentage_discount)/100) as price FROM ?:product_prices prices WHERE product_id IN (?a) AND lower_limit = ?i AND usergroup_id IN (?a)", 'product_id', $product_ids, 1, $usergroup_ids);
    $products = fn_array_merge($products, $prices);
}

function fn_smart_distribution_get_stickers_pre($params, $fields, &$condition, $lang_code) {
    if (isset($params['name'])) $condition .= db_quote(' AND ?:product_stickers.name = ?s', $params['name']);
}

function fn_smart_distribution_get_product_features($fields, $join, &$condition, $params)
{
    // [only vendor features]
    if (AREA == 'A' && fn_allowed_for('MULTIVENDOR')) {
        if (isset($params['product_id']) && !empty($params['product_id'])) {
            $company_id = db_get_field("SELECT company_id FROM ?:products WHERE product_id = ?i", $params['product_id']);
        } else {
            $company_id = Registry::get('runtime.company_id');
        }
        if (!empty($company_id)) $condition .= db_quote(" AND (pf.company_id = 0 OR pf.company_id = ?i)", Registry::get('runtime.company_id'));
    }
    // [/only vendor features]
}

function fn_timestamp_to_date_wo_time($timestamp) {
    return !empty($timestamp) ? date('d.m.Y', intval($timestamp)) : '';
}

function fn_smart_distribution_send_form(&$page_data, $form_values, $result, $from, $sender, $attachments, $is_html, $subject) {
    if (Tygh::$app['session']['auth']['user_id']) {
        $managers = fn_smart_distribution_get_managers(['user_id' => Tygh::$app['session']['auth']['user_id']]);
        if (!empty($managers)) {
            $page_data['form']['general'][FORM_RECIPIENT] = fn_array_column($managers, 'email', 'name');
        }
    }
}

function fn_smart_distribution_get_submitted_forms_pre($params, &$condition, $order_by, $limit) {
    if (fn_smart_distribution_is_manager(Tygh::$app['session']['auth']['user_id'])) {
        $customer_ids = db_get_fields('SELECT customer_id FROM ?:vendors_customers WHERE vendor_manager = ?i', Tygh::$app['session']['auth']['user_id']);
        $condition .= db_quote(' AND user_id IN (?a)', $customer_ids);
    }
}

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function fn_set_checkpoint() {
    static $prev_time;
    if (!$prev_time) {
        $prev_time = microtime_float();
    }
    $current_time = microtime_float();
    $time = $current_time - $prev_time;
    $prev_time = $current_time;
    return $time;
}

function fn_smart_distribution_mailer_send_post($_this, $transport, $message, $result, $area, $lang_code) {
    foreach ($result->getErrors() as $error) {
        fn_delete_notification_by_message(__('error_message_not_sent') . ' ' . $error);
    }
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
