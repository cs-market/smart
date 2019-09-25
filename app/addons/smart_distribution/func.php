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
	if (!empty($params['user_id'])) {
		$condition .= db_quote(" AND vc.customer_id = ?i", $params['user_id']);	
	}

	$managers = db_get_hash_array("SELECT DISTINCT(vc.vendor_manager) as user_id, u.email, IF(CONCAT(firstname, lastname) = '', email, CONCAT(firstname, ' ', lastname)) AS name, u.company_id FROM ?:vendors_customers AS vc LEFT JOIN ?:users AS u ON u.user_id = vc.vendor_manager WHERE 1 $condition", 'user_id');

	return $managers;
}

function fn_smart_distribution_get_users_pre(&$params, $auth, $items_per_page, $custom_view) {
	if (Registry::get('runtime.company_id')) {
		// сомнительно, проверить под юзером 1929 #21475
		//$params['company_id'] = Registry::get('runtime.company_id');
		$params['exclude_user_types'] = array('V');
	}
}
function fn_smart_distribution_get_users($params, $fields, $sortings, &$condition, &$join, $auth) {
	if (!empty($params['managers'])) {
		if (!is_array($params['managers'])) {
			$managers = explode(',', $params['managers']);
		}
		$join .= db_quote(' LEFT JOIN ?:vendors_customers ON ?:vendors_customers.customer_id = ?:users.user_id');
		$condition['vendor_manager'] = db_quote(' AND ?:vendors_customers.vendor_manager in (?a) ', $managers);
	}
	if (Registry::get('runtime.company_id') && isset($condition['users_company_id'])) {
		// сомнительно, проверить под юзером 1929 #21475
		unset($condition['users_company_id']);
		$condition['sd_condition'] = ' AND (' . fn_get_company_condition('?:users.company_id', false) . db_quote(" OR ?:users.user_id IN (?n) )", fn_get_company_customers_ids(Registry::get('runtime.company_id')));
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

function fn_smart_distribution_gather_additional_product_data_post(&$product, $auth, $params) {
	// for discount label in mobile application
	if (isset($product['discount']) && !( (float) $product['list_price'])) {
		$product['list_price'] = $product['base_price'];
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

function fn_smart_distribution_get_companies($params, &$fields, $sortings, $condition, $join, $auth, $lang_code, $group) {
	$fields[] = 'min_order_amount';
}

function fn_smart_distribution_get_default_usergroups(&$default_usergroups, $lang_code) {
	if (Registry::get('runtime.company_id')) {
		$default_usergroups = array();
	}
}

function fn_smart_distribution_api_handle_request($_this, $authorized) {
	//fn_write_r(date('H:m:s d/m/Y'), $_this->getRequest()->getResource() . ' ' . $_this->getRequest()->getMethod());
}

function fn_smart_distribution_api_send_response($this, $response, $authorized) {
	//fn_write_r($response->body);
}

function fn_write_r() { 
  static $count = 0; 
  $args = func_get_args(); 
  $fp = fopen('api_requests.html', 'a+'); 
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
	foreach ($product_data as $key => $product) {
		if (isset($product['product_code']) && $cart['user_data']['company_id'] && $key == $product['product_code']) {
			$product_id = db_get_field('SELECT product_id FROM ?:products WHERE company_id = ?i AND product_code = ?s', $cart['user_data']['company_id'], $key);
			if ($product_id) {
				$_product_data[$product_id] = $product;
			}
		} else {
			$_product_data[$key] = $product;
		}	
	}
	$product_data = $_product_data;
	$cart['skip_notification'] = true;
}

function fn_smart_distribution_get_profile_fields($location, $select, &$condition) {
	if (AREA == 'C' && in_array(Registry::get('runtime.controller'), array('checkout', 'profiles'))) {
		$stop_fields = array(
			's_address',
			'b_client_code',
			's_client_code',
			's_address_2',
			's_lastname'
		);
		if (Registry::get('runtime.mode') == 'add') {
			$stop_fields[] = 'company';
			$stop_fields[] = 'fax';
		} else {
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
function fn_smart_distribution_pre_update_order($cart, $order_id) {
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
}

// for exim to escape /n at the end
function fn_smart_distribution_get_company_id_by_name($company_name, &$condition) {
	$condition = str_replace('\\n', '', $condition);
}