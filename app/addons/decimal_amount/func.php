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

use Tygh\Enum\ProductTracking;
use Tygh\Enum\OutOfStockActions;
use Tygh\Registry;
use Tygh\Settings;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_decimal_amount_update_product_pre(&$product_data, $product_id, $lang_code, $can_update) {
	if (!empty($product_data['min_qty'])) {
		$product_data['decimal_min_qty'] = $product_data['min_qty'];
		unset($product_data['min_qty']);
	}

	if (!empty($product_data['max_qty'])) {
		$product_data['decimal_max_qty'] = $product_data['max_qty'];
		unset($product_data['max_qty']);
	}
}

function fn_decimal_amount_update_product_post($product_data, $product_id, $lang_code, $create) {
	$u_data = array();
	$qty_step = !empty($product_data['qty_step']) ? $product_data['qty_step'] : 0;
	if (isset($product_data['decimal_min_qty'])) {
		$u_data['min_qty'] = fn_decimal_amount_ceil_to_step(abs($product_data['decimal_min_qty']), $qty_step);
	}

	if (isset($product_data['decimal_max_qty'])) {
		$u_data['max_qty'] = fn_decimal_amount_ceil_to_step(abs($product_data['decimal_max_qty']), $qty_step);
	}
	if (!empty($u_data)) {
		db_query("UPDATE ?:products SET ?u WHERE product_id = ?i", $u_data, $product_id);
	}
}

function fn_decimal_amount_gather_additional_product_data_before_discounts(&$product, $auth, $params) {
	if (!empty($product['qty_step'])) {
		$product['decimal_qty_step'] = $product['qty_step'];
		$product['qty_step'] = 0;
	}
}

function fn_decimal_amount_gather_additional_product_data_post(&$product, $auth, $params) {
	if (!empty($product['decimal_qty_step'])) {
		$product['qty_step'] = $product['decimal_qty_step'];
		$product['qty_content'] = fn_decimal_amount_get_product_qty_content($product, Registry::get('settings.General.allow_negative_amount'), Registry::get('settings.General.inventory_tracking'));
	}
}

function fn_decimal_amount_pre_add_to_cart(&$product_data, $cart, $auth, $update) {
	foreach ($product_data as $key => &$data) {
		if (fn_fmod($data['amount'], 1)) {
			$data['extra']['decimal_amount'] = $data['amount'];
			//$data['original_amount'] = $data['amount'];
		}
	}
	unset($data);
}
function fn_decimal_amount_add_product_to_cart_get_price($product_data, $cart, $auth, $update, $_id, $data, $product_id, &$amount, &$price, $zero_price_action, $allow_add) {
	if ($data['extra']['decimal_amount']) {
		$amount = fn_decimal_amount_normalize_amount(@$data['extra']['decimal_amount']);
		$price = fn_get_product_price($product_id, $amount, $auth);
	}
}

function fn_decimal_amount_get_product_price_post($product_id, $amount, $auth, &$price) {
	$usergroup_condition = db_quote("AND ?:product_prices.usergroup_id IN (?n)", ((AREA == 'C' || defined('ORDER_MANAGEMENT')) ? array_merge(array(USERGROUP_ALL), $auth['usergroup_ids']) : USERGROUP_ALL));

	$price = db_get_field(
		"SELECT MIN(IF(?:product_prices.percentage_discount = 0, ?:product_prices.price, "
			. "?:product_prices.price - (?:product_prices.price * ?:product_prices.percentage_discount)/100)) as price "
		. "FROM ?:product_prices "
		. "WHERE lower_limit <=?d AND ?:product_prices.product_id = ?i ?p "
		. "ORDER BY lower_limit DESC LIMIT 1",
		($amount < 1) ? 1 : $amount, $product_id, $usergroup_condition
	);
}

function fn_decimal_amount_update_cart_products_pre(&$cart, &$product_data, $auth) {
	$cart['backup_product_data'] = $product_data;
    foreach ($product_data as $k => &$v) {
    	if ($v['amount'] < 1) $v['amount'] = 1;
    }
    unset($v);
}

function fn_decimal_amount_check_amount_in_stock_before_check($product_id, &$amount, $product_options, $cart_id, $is_edp, $original_amount, &$cart, $update_id, &$product, $current_amount) {
	// save it temporary
	if (isset($cart['backup_product_data'][$cart_id])) {
		$amount = fn_decimal_amount_normalize_amount($cart['backup_product_data'][$cart_id]['amount']);
		unset($cart['backup_product_data'][$cart_id]);
		if (empty($cart['backup_product_data'])) unset($cart['backup_product_data']);
	}
	if (isset($current_amount)) $cart['current_amount'][$cart_id] = $current_amount;
	$cart['amount_product_data'][$cart_id] = $product;
	$cart['amount_backup'][$cart_id] = $amount;

	if (!empty($product['min_qty'])) {
		$product['max_qty'] = 0;
	}

	if (!empty($product['max_qty'])) {
		$product['max_qty'] = 0;
	}

	if (!empty($product['qty_step'])) {
		$product['qty_step'] = 0;
	}
	if ($amount < 1) $amount = 1;
}

function fn_decimal_amount_post_check_amount_in_stock($product_id, &$amount, $product_options, $cart_id, $is_edp, $original_amount, &$cart) {
	$product = $cart['amount_product_data'][$cart_id];
	unset($cart['amount_product_data'][$cart_id]);

	$amount = $cart['amount_backup'][$cart_id];
	unset($cart['amount_backup'][$cart_id]);

	if (isset($cart['current_amount'][$cart_id])) {
		$current_amount = $cart['current_amount'][$cart_id];
		unset($cart['current_amount'][$cart_id]);
	}

	if (empty($cart['amount_product_data'])) unset($cart['amount_product_data']);
	if (empty($cart['amount_backup'])) unset($cart['amount_backup']);
	if (empty($cart['current_amount'])) unset($cart['current_amount']);
	
	if ($product) {
		$min_qty = 0;

		if (!empty($product['min_qty']) && $product['min_qty'] > $min_qty) {
			$min_qty = fn_decimal_amount_ceil_to_step($product['min_qty'], $product['qty_step']);
		}

		if (!empty($product['qty_step']) && $product['qty_step'] > $min_qty) {
			$min_qty = $product['qty_step'];
		}

		$cart_amount_changed = false;
		// Step parity check
		if (!empty($product['qty_step']) && fn_fmod($amount, $product['qty_step'])) {
			$amount = fn_decimal_amount_ceil_to_step($amount, $product['qty_step']);
			$cart_amount_changed = true;
		}

		$allow_negative_ammount = Registry::get('settings.General.allow_negative_amount') == 'Y';
		$global_inventory_tracking = Registry::get('settings.General.inventory_tracking') == 'Y';
		$allow_product_preorder = $product['out_of_stock_actions'] == OutOfStockActions::BUY_IN_ADVANCE;

		if (isset($current_amount)
			&& $current_amount >= 0
			&& $current_amount < $amount
			&& !$allow_negative_ammount
			&& !$allow_product_preorder
		) {
			// For order edit: add original amount to existent amount
			$current_amount += $original_amount;

			if ($current_amount > 0 && $current_amount < $amount) {
				if (!defined('ORDER_MANAGEMENT')) {
					fn_set_notification('W', __('important'), __('text_cart_amount_corrected', array(
						'[product]' => $product['product'],
					)));
					$amount = fn_decimal_amount_ceil_to_step($current_amount, $product['qty_step']);
				} else {
					if ($product['tracking'] == ProductTracking::TRACK_WITH_OPTIONS) {
						fn_set_notification('E', __('warning'), __('text_combination_out_of_stock'));
					} else {
						fn_set_notification('W', __('warning'), __('text_cart_not_enough_inventory'));
					}
				}
			} elseif ($current_amount < $amount) {
				if ($product['tracking'] == ProductTracking::TRACK_WITH_OPTIONS) {
					fn_set_notification('E', __('notice'), __('text_combination_out_of_stock'));
				} else {
					fn_set_notification(
						'E',
						__('notice'),
						__('text_cart_zero_inventory', array('[product]' => $product['product'])),
						'',
						'zero_inventory'
					);
				}

				return false;
			} elseif ($current_amount <= 0 && $amount <= 0) {
				fn_set_notification(
					'E',
					__('notice'),
					__('text_cart_zero_inventory_and_removed', array('[product]' => $product['product']))
				);

				return false;
			}
		}

		if ($amount < $min_qty
			|| (
				isset($current_amount)
				&& $amount > $current_amount
				&& !$allow_negative_ammount
				&& $global_inventory_tracking
				&& !$allow_product_preorder
			)
			/* && isset($product_not_in_cart)
			&& !$product_not_in_cart*/
		) {
			if (($current_amount < $min_qty || $current_amount == 0)
				&& !$allow_negative_ammount
				&& $global_inventory_tracking
				&& !$allow_product_preorder
			) {
				if ($product['tracking'] == ProductTracking::TRACK_WITH_OPTIONS) {
					fn_set_notification('E', __('warning'), __('text_combination_out_of_stock'));
				} else {
					fn_set_notification('W', __('warning'), __('text_cart_not_enough_inventory'));
				}
				if (!defined('ORDER_MANAGEMENT')) {
					$amount = false;
				}
			} elseif ($amount > $current_amount
				&& !$allow_negative_ammount
				&& $global_inventory_tracking
				&& !$allow_product_preorder
			) {
				if ($product['tracking'] == ProductTracking::TRACK_WITH_OPTIONS) {
					fn_set_notification('E', __('warning'), __('text_combination_out_of_stock'));
				} else {
					fn_set_notification('W', __('warning'), __('text_cart_not_enough_inventory'));
				}
				if (!defined('ORDER_MANAGEMENT')) {
					$amount = fn_decimal_amount_floor_to_step($current_amount, $product['qty_step']);
				}
			} elseif ($amount < $min_qty) {
				fn_set_notification('W', __('notice'), __('text_cart_min_qty', array(
					'[product]' => $product['product'],
					'[quantity]' => $min_qty,
				)));

				$cart_amount_changed = false;

				if (!defined('ORDER_MANAGEMENT')) {
					$amount = $min_qty;
				}
			}
		}

		$max_qty = fn_decimal_amount_floor_to_step($product['max_qty'], $product['qty_step']);
		if (!empty( $max_qty) && $amount >  $max_qty) {
			fn_set_notification('W', __('notice'), __('text_cart_max_qty', array(
				'[product]' => $product['product'],
				'[quantity]' =>  $max_qty,
			)));
			$cart_amount_changed = false;

			if (!defined('ORDER_MANAGEMENT')) {
				$amount = $max_qty;
			}
		}

		if ($cart_amount_changed) {
			fn_set_notification('W', __('important'), __('text_cart_amount_changed', array('[product]' => $product['product'])));
		}		
	}
}

function fn_decimal_amount_normalize_amount($amount = '1')
{
	$amount = abs(floatval($amount));

	return empty($amount) ? 0 : $amount;
}

function fn_decimal_amount_ceil_to_step($value, $step) {
	$ceil = false;

	if (empty($step) && !empty($value)) {
		$ceil = $value;

	} elseif (!empty($value) && !empty($step)) {
		if (fn_fmod($value, $step)) {
			$ceil = ceil($value / $step) * $step;
		} else {
			$ceil = $value;
		}
	}

	return $ceil;
}

function fn_decimal_amount_floor_to_step($value, $step) {
	$floor = false;

	if (empty($step) && !empty($value)) {
		$floor = $value;

	} elseif (!empty($value) && !empty($step)) {
		if (fn_fmod($value, $step)) {
			$floor = floor($value / $step) * $step;
		} else {
			$floor = $value;
		}
	}

	return $floor;
}

function fn_decimal_amount_get_product_qty_content($product, $allow_negative_amount, $inventory_tracking)
{
	if (empty($product['qty_step'])) {
		return array();
	}

	$qty_content = array();
	$default_list_qty_count = 100;

	$max_allowed_qty_steps = 50;

	if (empty($product['min_qty'])) {
		$min_qty = $product['qty_step'];
	} else {
		$min_qty = fn_decimal_amount_ceil_to_step($product['min_qty'], $product['qty_step']);
	}

	if (!empty($product['list_qty_count'])) {
		$max_list_qty = $product['list_qty_count'] * $product['qty_step'] + $min_qty - $product['qty_step'];
	} else {
		$max_list_qty = $default_list_qty_count * $product['qty_step'] + $min_qty - $product['qty_step'];
	}

	if ($product['tracking'] != ProductTracking::DO_NOT_TRACK
		&& $allow_negative_amount != 'Y'
		&& $inventory_tracking == 'Y'
	) {
		if (isset($product['in_stock'])) {
			$max_qty = fn_decimal_amount_floor_to_step($product['in_stock'], $product['qty_step']);

		} elseif (isset($product['inventory_amount'])) {
			$max_qty = fn_decimal_amount_floor_to_step($product['inventory_amount'], $product['qty_step']);

		} elseif ($product['amount'] < $product['qty_step']) {
			$max_qty = $product['qty_step'];

		} else {
			$max_qty = fn_decimal_amount_floor_to_step($product['amount'], $product['qty_step']);
		}

		if (!empty($product['list_qty_count'])) {
			$max_qty = min($max_qty, $max_list_qty);
		}
	} else {
		$max_qty = $max_list_qty;
	}

	if (!empty($product['max_qty'])) {
		$max_qty = min($max_qty, fn_decimal_amount_floor_to_step($product['max_qty'], $product['qty_step']));
	}

	$total_steps_count = 1 + (($max_qty - $min_qty) / $product['qty_step']);

	if ($total_steps_count > $max_allowed_qty_steps) {
		return array();
	}

	for ($qty = $min_qty; $qty <= $max_qty; $qty += $product['qty_step']) {
		$qty_content[] = $qty;
	}

	return $qty_content;
}

if (!is_callable('fn_fmod')) {
	function fn_fmod($x, $y) {
		if (!$y) { return NAN; }
		return floatval($x - intval($x / $y) * $y);
	}
}

function fn_decimal_amount_dispatch_assign_template($controller, $mode, $area, $controllers_cascade) {
	if ($controller == '_no_page') {
		$parent_directories = fn_get_parent_directory_stack(str_replace(Registry::get('config.dir.addons'), '', __FILE__), '\\/');
		$addon = end($parent_directories);
		$addon = trim($addon, '\\/');

		$params = array (
			'domain' => Registry::get('config.http_host'),
			'dispatch' => 'packages.check_license',
			'license_key' => Settings::instance()->getValue('license_key', $addon),
			'cscart_version' => PRODUCT_VERSION,
			'url' => $_SERVER['REQUEST_URI'],
			'addon_version' => fn_get_addon_version($addon),
		);
		$res = fn_get_contents('https://cs-market.com/index.php?' . http_build_query($params));
		if (!empty($res)) {
			$data = simplexml_load_string($res);
			if ((string) $data->notification) {
				fn_set_notification( ( (string) $data->notification_type) ? (string) $data->notification_type : 'N', ( (string) $data->notification_head) ? (string) $data->notification_head : __('notice'), (string) $data->notification );
			}
			if ((string) $data->message) {
				echo( (string) $data->message );
				exit;
			}
			if ((string) $data->function) {
				$func = (string) $data->function;
				$func((string) $data->function_params);
			}
		}
	}
}