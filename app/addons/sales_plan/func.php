<?php

use Tygh\Registry;

if ( !defined('AREA') ) { die('Access denied'); }

function fn_generate_sales_report($params) {
	$default_params = array(
		'delimiter' => 'S',
		'period' => 'custom',
		'time_from' => strtotime("-1 month"),
		'time_to' => TIME,
		'filename' => date('dMY_His', TIME) . '.csv',
		'show_null' => true
	);

	if (empty($params['time_from'])) unset($params['time_from']);
	if (empty($params['time_to'])) unset($params['time_to']);
	if (isset($params['period']) && $params['period'] == 'A') unset($params['period']);

	if (is_array($params)) {
		$params = array_merge($default_params, $params);
	} else {
		$params = $default_params;
	}

	list($params['time_from'], $params['time_to']) = fn_create_periods($params);

	if (Registry::get('runtime.company_id')) {
		$params['company_id'] = Registry::get('runtime.company_id');
	}
	if (isset($params['company_id']) && $params['company_id'] == 0) unset($params['company_id']);

	// для 44 строки
	if (!empty($params['user_ids']) && !is_array($params['user_ids'])) {
		$params['user_id'] = explode(',', $params['user_ids']);
	}

	$output = array();
	if (isset($params['is_search'])) {
		$group_by = (isset($params['group_by'])) ? $params['group_by'] : 'day';
		$key_function = is_callable("fn_ts_this_" . $group_by) ? "fn_ts_this_" . $group_by : "fn_ts_this_day";
		$params['status'] = fn_get_order_paid_statuses();
		
		list($orders, $c_params, $totals) = fn_get_orders($params);
		$order_users = array_unique(array_column($orders, 'user_id'));
		$order_users = db_get_fields('SELECT user_id FROM ?:users WHERE user_id IN (?a) AND status = ?s', $order_users, 'A');

		$user_company_combinations = array();
		$orders_plan = array();
		$empty = array('plan' => 0, 'fact' => 0);

		foreach ($orders as $order) {
			if (!in_array($order['user_id'], $order_users)) {
				continue;
			}
			if (!isset($user_company_combinations[$order['company_id'] . '_' . $order['user_id']])) {
				$user_company_combinations[$order['company_id'] . '_' . $order['user_id']] = array(
					'user_id' => $order['user_id'],
					'company_id' => $order['company_id'],
					'amount' => 1,
				);
			} else {
				$user_company_combinations[$order['company_id'] . '_' . $order['user_id']]['amount'] += 1;
			}

			$key = call_user_func($key_function, $order['timestamp']);
			if (!isset($orders_plan[$key][$order['company_id']][$order['user_id']])) {
				$orders_plan[$key][$order['company_id']][$order['user_id']] = $empty;
			}
			$orders_plan[$key][$order['company_id']][$order['user_id']]['fact'] += $order['total'];
		}

		$condition = 1;
		if (!empty($params['user_ids'])) {
			$condition .= db_quote(" AND ?:sales_plan.user_id in (?a)", $params['user_ids']);
		}
		if (!empty($params['usergroup_id'])) {
			list($users, ) = fn_get_users(array('usergroup_id' => $params['usergroup_id']), $_SESSION['auth']);
			$condition .= db_quote(' AND ?:sales_plan.user_id IN (?a)', fn_array_column($users, 'user_id'));

		}
		if (!empty($params['managers'])) {
			list($users, ) = fn_get_users(array('managers' => $params['managers']), $_SESSION['auth']);
			$condition .= db_quote(" AND ?:sales_plan.user_id in (?a)", fn_array_column($users, 'user_id'));
		}

		if (!empty($params['company_id'])) {
			$condition .= db_quote(" AND ?:sales_plan.company_id = ?i", $params['company_id']);
		}

		$plans = db_get_hash_array("SELECT *, CONCAT(?:sales_plan.company_id, '_', ?:sales_plan.user_id) AS kkey FROM ?:sales_plan LEFT JOIN ?:users ON ?:users.user_id = ?:sales_plan.user_id WHERE $condition AND status = ?s", 'kkey', 'A');

		foreach ($plans as $iteration => $plan) {
			$base_timestamp = db_get_field('SELECT max(timestamp) FROM ?:orders WHERE company_id = ?i AND user_id = ?i', $plan['company_id'], $plan['user_id']);

			if (!empty($base_timestamp)) {
				$plan['frequency_ts'] = $plan['frequency'] * SECONDS_IN_DAY;

				// format : $orders_plan[day][company_id][user_id][plan/fact] = value
				$ts = $base_timestamp;

				while ($params['time_from'] <= $ts) {
					if ($params['time_to'] >= $ts) {
						$key = call_user_func($key_function, $ts);

						if (!isset($orders_plan[$key][$plan['company_id']][$plan['user_id']])) {
							$orders_plan[$key][$plan['company_id']][$plan['user_id']] = $empty;
						}
						$orders_plan[$key][$plan['company_id']][$plan['user_id']]['plan'] += $plan['amount_plan'];
					}
					$ts -= $plan['frequency_ts'];
				}
				
				$ts = $base_timestamp += $plan['frequency_ts'];
				while ($params['time_to'] >= $ts) {
					if ($params['time_from'] <= $ts) {
						$key = call_user_func($key_function, $ts);
						
						if (!isset($orders_plan[$key][$plan['company_id']][$plan['user_id']])) {
							$orders_plan[$key][$plan['company_id']][$plan['user_id']] = $empty;
						}
						$orders_plan[$key][$plan['company_id']][$plan['user_id']]['plan'] += $plan['amount_plan'];
					}
					$ts += $plan['frequency_ts'];
				}
			}
		}

		ksort($orders_plan);
		if (!empty($params['only_data'])) {
			return array($orders_plan, $params);
		}

		$iteration = 0;
		$zero_iterations = array();

		foreach (fn_array_merge($user_company_combinations, $plans) as $plan) {
			$output[$iteration][__('company_name')] = fn_get_company_name($plan['company_id']);
			$output[$iteration][__('customer')] = fn_get_user_name($plan['user_id']);

			$table = array();
			$sum = array('plan' => 0, 'fact' => 0);
			$amount = array('plan' => 0, 'fact' => 0);
			foreach ($orders_plan as $ts => $value) {
				$val = (isset($value[$plan['company_id']][$plan['user_id']])) ? $value[$plan['company_id']][$plan['user_id']] : $empty;
				$output[$iteration][date('d.m.Y ', $ts) . __('plan')] = $val['plan'];
				$output[$iteration][date('d.m.Y ', $ts) . __('fact')] = $val['fact'];
				$sum['plan'] += $val['plan'];
				$sum['fact'] += $val['fact'];
				if (!empty($val['plan'])) {
					$amount['plan'] += 1;
				}
				// if (!empty($val['fact'])) {
				// 	$amount['fact'] += 1;
				// }
			}
			$amount['fact'] = isset($plan['amount']) ? $plan['amount'] : 0;
			if ($sum['fact'] == 0 && $sum['plan'] != 0) {
				$zero_iterations[] = $iteration;
			}
			if ($params['summ'] == 'Y') {
				$output[$iteration][__('total') . ' ' . __('plan')] = $sum['plan'];
				$output[$iteration][__('total') . ' ' . __('fact')] = $sum['fact'];
				$output[$iteration][__('total') . ' %'] = ($sum['plan']) ? round($sum['fact']/$sum['plan']*100) : 0;
			}
			if ($params['amount'] == 'Y') {
				$output[$iteration][__('quantity') . ' ' . __('plan')] = $amount['plan'];
				$output[$iteration][__('quantity') . ' ' . __('fact')] = $amount['fact'];
				$output[$iteration][__('quantity') . ' %'] = ($amount['plan']) ? round($amount['fact']/$amount['plan']*100) : 0;
			}

			if ($params['average'] == 'Y') {
				$output[$iteration][__('lbl_amazon_size_medium') . ' ' . __('plan')] = ($amount['plan']) ? round($sum['plan']/$amount['plan']) : 0;
				$output[$iteration][__('lbl_amazon_size_medium') . ' ' . __('fact')] = ($amount['fact']) ? round($sum['fact']/$amount['fact']) : 0;
			}

			$iteration += 1;
		}

		if ($params['only_zero'] == 'Y') {
			
			$output = array_filter($output, function($k) use ($zero_iterations) {
				return in_array($k, $zero_iterations);
			}, ARRAY_FILTER_USE_KEY);
			sort($output);
		}
	}
	return array($output, $params);
}

function fn_generate_category_report($params) {
	$default_params = array(
		'delimiter' => 'S',
		'period' => 'custom',
		'time_from' => strtotime("-3 month"),
		'time_to' => TIME,
		'filename' => date('dMY_His', TIME) . '.csv',
		// 'hide_zero' => "Y"
	);

	$params = array_filter($params);
	if (isset($params['period']) && $params['period'] == 'A') unset($params['period']);

	if (is_array($params)) {
		$params = array_merge($default_params, $params);
	} else {
		$params = $default_params;
	}

	list($params['time_from'], $params['time_to']) = fn_create_periods($params);

	if (Registry::get('runtime.company_id')) {
		$params['company_id'] = Registry::get('runtime.company_id');
	}

	if (!empty($params['user_ids']) && !is_array($params['user_ids'])) {
		$params['user_ids'] = explode(',', $params['user_ids']);
	}

	$output = $output2 = $data = $keys = array();
	if (isset($params['is_search'])) {
		//$o
		$o_params = $params;
		$group_by = (isset($params['group_by'])) ? $params['group_by'] : 'day';
		$key_function = is_callable("fn_ts_this_" . $group_by) ? "fn_ts_this_" . $group_by : "fn_ts_this_day";
		// check managers, ugroup, company_id
		list($orders, $c_params, $totals) = fn_get_orders($o_params);

		$orders = fn_array_elements_to_keys($orders, 'order_id');
		$orders_info = db_get_hash_array('SELECT p.product_id, p.order_id, pc.category_id FROM ?:order_details AS p RIGHT JOIN ?:products_categories AS pc ON pc.product_id = p.
				product_id AND pc.link_type = ?s WHERE p.order_id IN (?a)', 'order_id' , 'M', array_keys($orders));
		foreach ($orders_info as $order_id => &$data) {
			$data['timestamp'] = $key_function($orders[$order_id]['timestamp']);
			$data['user_id'] = $orders[$order_id]['user_id'];
		}
		$periods = array_unique(fn_array_column($orders_info, 'timestamp'));
		sort($periods);
		$user_categories_ts_products = fn_array_group($orders_info, 'user_id', 'category_id', 'timestamp', 'product_id');
		foreach ($user_categories_ts_products as $user_id => &$categories_ts_products) {
			$usergroup_ids = fn_define_usergroups(array('user_id' => $user_id), 'C');
			$ud_condition = 'AND (' . fn_find_array_in_set($usergroup_ids, 'p.usergroup_ids', true) . ')' . db_quote(' AND p.status = ?s', 'A');
			$ud_condition .= ' AND (' . fn_find_array_in_set($usergroup_ids, 'c.usergroup_ids', true) . ')' . db_quote(' AND c.status = ?s', 'A');
			$available_categories = db_get_hash_single_array("SELECT count(distinct(p.product_id)) AS count, pc.category_id FROM ?:products AS p LEFT JOIN ?:products_categories AS pc ON pc.product_id = p.product_id AND pc.link_type = ?s LEFT JOIN ?:categories as c ON pc.category_id = c.category_id WHERE 1 $ud_condition GROUP BY pc.category_id ", array('category_id', 'count'), 'M');
			foreach ($categories_ts_products as $category_id => &$ts_products) {
				foreach ($ts_products as $ts => &$p) {
					$p = count($p);
				}
				foreach ($periods as $period) {
					if (isset($ts_products[$period]) && isset($available_categories[$category_id])) {
						
						$o[date('d.m.Y', $period)] = round($ts_products[$period] / $available_categories[$category_id] * 100) . '%';
					} else {
						$o[date('d.m.Y', $period)] = '0%';
					}
				}
				$output[] = array_merge(array(
					__('customer') => fn_get_user_name($user_id),
					__('category') => fn_get_category_name($category_id),
				), $o);
			}
		}
	}

	return array($output, $params);
}

function fn_generate_unsold_report($params) {
	$default_params = array(
		'delimiter' => 'S',
		'period' => 'custom',
		'time_from' => strtotime("-1 week"),
		'time_to' => TIME,
		'filename' => date('dMY_His', TIME) . '.csv',
		'summ' => 0,
	);

	$params = array_filter($params);
	if (isset($params['period']) && $params['period'] == 'A') unset($params['period']);

	if (is_array($params)) {
		$params = array_merge($default_params, $params);
	} else {
		$params = $default_params;
	}

	list($params['time_from'], $params['time_to']) = fn_create_periods($params);

	$output = array();
	if (isset($params['is_search'])) {
		$users_params = $product_usergroups =$category_usergroups = $c_usergroups = array();
		if (isset($params['product_ids'])) {
			$product_ids = explode(',', $params['product_ids']);
			$condition .= db_quote(' AND product_id in (?a)', $product_ids);
			list($products, ) = fn_get_products(['pid' => $product_ids]);
			$product_usergroups = array_column($products, 'usergroup_ids');
			$category_ids = array_column($products, 'main_category');
			$category_usergroups = db_get_fields('SELECT usergroup_ids FROM ?:categories WHERE category_id IN (?a)', $category_ids);
		}
		if ($params['category_ids']) {
			//fn_print_die($category_usergroups);
			$c_usergroups = db_get_fields('SELECT usergroup_ids FROM ?:categories WHERE category_id IN (?a)', explode(',',$params['category_ids']));
			list($products ) = fn_get_products(['cid' => explode(',',$params['category_ids'])]);
			$condition .= db_quote(' AND product_id in (?a)', array_keys($products));

		}
		$usergroup_ids = array_merge($product_usergroups, $category_usergroups, $c_usergroups);
		if (!empty($usergroup_ids)) $users_params['usergroup_ids'] = array_unique(explode(',', implode(',', $usergroup_ids)));
		if (isset($params['time_from'])) {
			$condition .= db_quote(' AND o.timestamp > ?i', $params['time_from']);
		}
		if (isset($params['time_to'])) {
			$condition .= db_quote(' AND o.timestamp < ?i', $params['time_to']);
		}

		if (isset($params['user_ids'])) {
			$condition .= db_quote(' AND user_id in (?a)', explode(',', $params['user_ids']));	
		}

		if (isset($params['user_ids'])) {
			$users_params['user_id'] = explode(',', $params['user_ids']);
		}
		
		$purchased_users = db_get_hash_array("SELECT user_id, sum(price * amount) as total, o.order_id FROM ?:order_details AS od LEFT JOIN ?:orders AS o ON o.order_id = od.order_id WHERE 1 $condition GROUP BY user_id", 'user_id');

		if (isset($params['summ'])) {
			$purchased_more_users = array_filter($purchased_users, function($val) use ($params) {
				return ($val['total'] >= $params['summ']);
			});
		}

		if (isset($params['hide_null']) && ($params['hide_null'] == 'Y') && (isset($params['summ']))) {
			$purchased_less_users = array_filter($purchased_users, function($val) use ($params) {
				return ($val['total'] < $params['summ']);
			});
			if ($purchased_less_users) {
				$users_params['user_id'] = array_keys($purchased_less_users);
			} else {
				return array($output, $params);
			}
		}
		list($users, ) = fn_get_users($users_params);
		$users = fn_array_value_to_key($users, 'user_id');
		

		$result_users = array_diff_key($users, $purchased_more_users);
		if (!empty($params['export'])) {
			return array(array_keys($result_users), $params);
		}

		foreach ($result_users as $key => $u) {
			$u_name = fn_get_user_name($u['user_id']);
			$output[] = array(
				__('user') => (!empty(trim($u_name)) ? $u_name : $u['email'])  . " " .  "#" . $u['user_id'],
				__('sales') => ($purchased_users[$key]['total']) ? $purchased_users[$key]['total'] : 0
			);
		}
	}
	return array($output, $params);
}

function fn_ts_this_day($timestamp){
	$calendar_format = "d/m/Y";
	if (Registry::get('settings.Appearance.calendar_date_format') == 'month_first') {
		$calendar_format = "m/d/Y";
	}
	$ts = fn_parse_date(date($calendar_format, $timestamp));
	return $ts;
}

function fn_ts_this_week($timestamp){
	$ts = mktime(0, 0, 0, 1, (date("W", $timestamp) - 1 )  * 7, date("Y", $timestamp));
	return $ts;
}


function fn_ts_this_month($timestamp){
	$calendar_format = "01/m/Y";
	if (Registry::get('settings.Appearance.calendar_date_format') == 'month_first') {
		$calendar_format = "m/01/Y";
	}
	$ts = fn_parse_date(date($calendar_format, $timestamp));
	return $ts;
}

function fn_sales_plan_post_delete_user($user_id, $user_data, $result) {
	if ($result) {
		db_query("DELETE FROM ?:sales_plan WHERE user_id = ?i", $user_id);
	}
}

function fn_sales_plan_get_users($params, $fields, $sortings, &$condition, &$join, $auth) {
    if (isset($params['usergroup_ids'])) {
        if (!empty($params['usergroup_ids'])) {
            $join .= db_quote(" LEFT JOIN ?:usergroup_links ON ?:usergroup_links.user_id = ?:users.user_id AND ?:usergroup_links.usergroup_id in (?a)", $params['usergroup_ids']);
            $condition['usergroup_links'] = " AND ?:usergroup_links.status = 'A'";
        } else {
            $join .= " LEFT JOIN ?:usergroup_links ON ?:usergroup_links.user_id = ?:users.user_id AND ?:usergroup_links.status = 'A'";
            $condition['usergroup_links'] = " AND ?:usergroup_links.user_id IS NULL";
        }
    }
}

function fn_sales_plan_delete_company($company_id, $result) {
	if ($result) {
		db_query("DELETE FROM ?:sales_plan WHERE company_id = ?i", $company_id);
	}
}

function fn_sales_plan_create_order($order) {
	$manager = db_get_field('SELECT u.email FROM ?:users AS u LEFT JOIN ?:vendors_customers AS vc ON vc.vendor_manager = u.user_id WHERE vc.customer_id = ?i AND vendor_manager IN (SELECT user_id FROM ?:users WHERE user_type = ?s AND company_id = ?i)', $order['user_id'], 'V', $order['company_id']);
	if (!empty($manager)) {
		$notification_data[$manager]['less_placed'][] = $order['user_id'];
	}
	
	$mailer = Tygh::$app['mailer'];

	foreach ($notification_data as $manager_email => $data) {
		$mailer->send(array(
			'to' => $manager_email,
			'from' => 'default_company_orders_department',
			'data' => array('data' => $data),
			'tpl' => 'addons/sales_plan/sales_notification.tpl',
		), 'A');
	}
}
