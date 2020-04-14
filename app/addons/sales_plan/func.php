<?php

use Tygh\Registry;

if ( !defined('AREA') ) { die('Access denied'); }

require_once(Registry::get('config.dir.functions') . 'fn.sales_reports.php');

function fn_generate_sales_report_old($params) {
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
			$condition .= db_quote(" AND ?:sales_plan.user_id in (?a)", explode(',', $params['user_ids']));
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

		$plans = db_get_hash_array("SELECT ?:sales_plan.*, CONCAT(?:sales_plan.company_id, '_', ?:sales_plan.user_id) AS kkey FROM ?:sales_plan LEFT JOIN ?:users ON ?:users.user_id = ?:sales_plan.user_id WHERE $condition AND status = ?s", 'kkey', 'A');

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
			$user = fn_get_user_info($plan['user_id']);
			$output[$iteration][__('customer')] = $user['firstname'];//fn_get_user_name($plan['user_id']);
			$output[$iteration][__('address')] = $user['b_address'];
			$output[$iteration][__('code')] = $user['fields'][38];

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

function fn_generate_sales_report($params) {
	$default_params = array(
		'delimiter' => 'S',
		'period' => 'custom',
		'time_from' => strtotime("-1 month"),
		'time_to' => TIME,
		'filename' => date('dMY_His', TIME) . '.csv',
		'show_null' => true,
		'group_by' => 'day'
	);
		
	if (isset($params['period']) && $params['period'] == 'A') unset($params['period']);
	list($params['time_from'], $params['time_to']) = fn_create_periods($params);
	if (empty($params['time_from'])) unset($params['time_from']);
	if (empty($params['time_to'])) unset($params['time_to']);

	if ($params['time_to'] > time()) $params['time_to'] = time();

	if (is_array($params)) {
		$params = array_merge($default_params, $params);
	} else {
		$params = $default_params;
	}

	if (Registry::get('runtime.company_id')) {
		$params['company_id'] = Registry::get('runtime.company_id');
	}

	$output = array();
	if (isset($params['is_search'])) {
		$key_function = is_callable("fn_ts_this_" . $params['group_by']) ? "fn_ts_this_" . $params['group_by'] : 'fn_ts_this_day';

		$interval_id = db_get_field('SELECT interval_id FROM ?:sales_reports_intervals WHERE interval_code = ?s', $params['group_by']);

		$intervals = fn_check_intervals($interval_id, $params['time_from'], $params['time_to']);
		if ($interval_id == 5) {
			foreach ($intervals as &$interval) {
				$interval['description'] = date('d.m.Y', $interval['time_from']);
			}
		}
		unset($interval);

		$elements_fields = $elements_condition = $elements_join = array();
		$elements_group = '';

		$elements_fields['default'] = 'u.user_id, u.firstname';
		$elements_condition['default'] = db_quote(' AND u.status = ?s AND u.user_type = ?s', 'A', 'C');

		if (!empty($params['user_ids'])) {
			$elements_condition['user_id'] = db_quote(' AND u.user_id IN (?a)', explode(',', $params['user_ids']));
		}
		if (!empty($params['manager'])) {
			$elements_join['vendors_customers'] = db_quote(' LEFT JOIN ?:vendors_customers AS vc ON vc.customer_id = u.user_id ');
			$elements_condition['vendor_manager'] = db_quote(' AND vc.vendor_manager = ?i', $params['manager']);
		}
		if (!empty($params['usergroup_id'])) {
			$elements_join['usergroup_links'] .= db_quote(" LEFT JOIN ?:usergroup_links AS ul ON ul.user_id = u.user_id AND ul.usergroup_id = ?i", $params['usergroup_id']);
			$elements_condition['usergroup_links'] = " AND ul.status = 'A'";
		}
		if (!empty($params['company_id'])) {
			list($users, ) = fn_get_users(array('company_id' => $params['company_id']), $_SESSION['auth']);
			if (!empty($users)) $elements_condition['company_user_id'] = db_quote(' AND u.user_id IN (?a)', fn_array_column($users, 'user_id'));
		}
		if ($params['with_purchases'] == 'Y') {
			$elements_join['orders'] = ' LEFT JOIN ?:orders AS o ON o.user_id = u.user_id ';
			$elements_condition['orders'] = db_quote(' AND o.timestamp BETWEEN ?i AND ?i', $params['time_from'], $params['time_to']);
			$elements_group = ' GROUP BY o.user_id';
		}

		$elements = db_get_array(
			"SELECT " . implode(' ', $elements_fields)
			. " FROM ?:users as u"
			. implode(' ', $elements_join)
			. " WHERE 1 " . implode(' ', $elements_condition)
			. $elements_group
			. " ORDER BY firstname ASC, user_id"
		);

		$time_condition = db_quote(" timestamp BETWEEN ?i AND ?i", $params['time_from'], $params['time_to']);
		$group_condition = ' GROUP BY `interval`';
		if ($params['group_by'] == 'year') {
			$add_field = db_quote(", DATE_FORMAT(FROM_UNIXTIME(timestamp), '%Y') as `interval`, timestamp");
		} elseif ($params['group_by'] == 'month') {
			$add_field = db_quote(", DATE_FORMAT(FROM_UNIXTIME(timestamp), '%Y-%m') as `interval`, timestamp");
		} elseif ($params['group_by'] == 'week') {
			$add_field = db_quote(", DATE_FORMAT(FROM_UNIXTIME(timestamp), '%Y-%m-%u') as `interval`, timestamp");
		} elseif ($params['group_by'] == 'day') {
			$add_field = db_quote(", DATE_FORMAT(FROM_UNIXTIME(timestamp), '%Y-%m-%d') as `interval`, timestamp");
		} else {
			$add_field = db_quote(", 1 as `interval`, `timestamp`");
			$group_condition = '';
		}

		foreach ($elements as $element) {
			// add company_id condition
			if (!empty($params['company_id'])) {
				$company_condition = db_quote(' AND ?:orders.company_id = ?i', $params['company_id']);
			}
			$fact = db_get_hash_array("SELECT SUM(total) as total $add_field, count(order_id) as count FROM ?:orders WHERE user_id = ?i AND $time_condition AND ?:orders.status != 'T' AND ?:orders.status != 'I' AND ?:orders.is_parent_order != 'Y' $company_condition $group_condition", 'interval', $element['user_id']);

			if ($params['only_zero'] == 'Y' && count($fact) == count($intervals)) {
				continue;
			}

			$plan = array(0);
			if ($params['show_plan'] == 'Y' && $element['frequency']) {
				$base_timestamp = max(fn_array_column($fact, 'timestamp'));
				$element['frequency_ts'] = $element['frequency'] * SECONDS_IN_DAY;

				while ($params['time_from'] <= $base_timestamp) {
					//if ($params['time_to'] >= $ts) {
						$key = call_user_func($key_function, $base_timestamp);
						$plan[$key] += $element['amount_plan'];
						/*if (!isset($orders_plan[$key][$plan['company_id']][$plan['user_id']])) {
							$orders_plan[$key][$plan['company_id']][$plan['user_id']] = $empty;
						}
						$orders_plan[$key][$plan['company_id']][$plan['user_id']]['plan'] += $plan['amount_plan'];*/
					//}
					$base_timestamp -= $element['frequency_ts'];
				}
			}

			$user = fn_get_user_info($element['user_id']);
			$row = array();
			$row[__('company_name')] = ($user['company_id']) ? fn_get_company_name($user['company_id']) : '-';
			$row[__('date')] = date('d.m.Y', $user['timestamp']);
			$row[__('customer')] = $user['firstname'] . (($params['show_user_id'] == 'Y') ? ' #' . $element['user_id'] : '');//fn_get_user_name($plan['user_id']);
			$row[__('address')] = $user['b_address'];
			$row[__('code')] = $user['fields'][38];
			foreach ($intervals as $interval) {
				$f = $interval['description'];
				if ($params['show_plan'] == 'Y') {
					$p = __('plan') . ' ' . $f;
					$f = __('fact') . ' ' . $f;
					if (!empty($plan)) {
						foreach ($plan as $ts => $value) {
							if ($ts['timestamp'] >= $interval['time_from'] && $ts <= $interval['time_to']) {
								$row[$p] = $value;
								break;
							}
						}
					}
					if (!isset($row[$p])) {
						$row[$p] = 0;
					}
				}

				if (!empty($fact)) {
					foreach ($fact as $interval_data) {
						if ($interval_data['timestamp'] >= $interval['time_from'] && $interval_data['timestamp'] <= $interval['time_to']) {
							$row[$f] = $interval_data['total'];
							break;
						}
					}
				}

				if (!isset($row[$f])) {
					$row[$f] = 0;
				}
			}
			if ($params['summ'] == 'Y') {
				$f = array_sum(fn_array_column($fact, 'total'));
				$p = array_sum($plan);

				if ($params['show_plan'] == 'Y') {
					$row[__('total') . ' ' . __('fact')] = $f;
					$row[__('total') . ' ' . __('plan')] = $p;
					$row[__('total') . ' %'] = ($p) ? round($f/$p*100) : 0;
				} else {
					$row[__('total')] = $f;
				}
			}
			if ($params['amount'] == 'Y') {
				$f = array_sum(fn_array_column($fact, 'count'));
				$row[__('quantity')] = $f;
			}

			$output[] = $row;
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
				$user = fn_get_user_info($user_id);
				$output[] = array_merge(array(
					__('customer') => $user['firstname'],
					__('address') => $user['b_address'],
					__('code') => $user['fields'][38],
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

	if (Registry::get('runtime.company_id')) {
		$params['company_id'] = Registry::get('runtime.company_id');
	}

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
			$user = fn_get_user_info($u['user_id']);
			$output[] = array(
				__('user') => (!empty(trim($user['firstname'])) ? $user['firstname'] : $user['email'])  . " " .  "#" . $user['user_id'],
				__('address') => $user['b_address'],
				__('code') => $user['fields'][38],
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
	$manager = db_get_field("SELECT u.email FROM ?:users AS u LEFT JOIN ?:vendors_customers AS vc ON vc.vendor_manager = u.user_id WHERE vc.customer_id = ?i AND vendor_manager IN (SELECT user_id FROM ?:users LEFT JOIN ?:companies ON ?:users.company_id = ?:companies.company_id WHERE user_type = ?s AND ?:users.company_id = ?i AND ?:companies.notify_manager_order_insufficient = 'Y')", $order['user_id'], 'V', $order['company_id']);

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
