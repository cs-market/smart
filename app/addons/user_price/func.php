<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

//	[HOOKs]
function fn_user_price_update_product_post($product_data, $product_id, $lang_code, $create)
{
	if (isset($product_data['user_price'])) {
		fn_update_product_user_price($product_id, $product_data['user_price']);
	}
}

function fn_user_price_get_product_data_post(&$product_data, $auth, $preview, $lang_code)
{
	$product_data['user_price'] = fn_get_product_user_price($product_data['product_id']);
}

function fn_user_price_get_products_post(&$products, $params, $lang_code)
{
	$product_ids = array_keys($products);

	if (!$product_ids) {
		return true;
	}

	$user_prices = fn_get_product_user_price($product_ids);

	foreach ($user_prices as $user_price) {
		$products[$user_price['product_id']]['user_price'][] = $user_price;
	}
}

function fn_user_price_gather_additional_product_data_before_discounts(&$product, $auth, $params)
{
	if (isset($product['user_price']) && !empty($product['user_price']) && AREA == 'C') {
		$product['price'] = $product['user_price'][0]['price'];
		$product['base_price'] = $product['price'];
		unset($product['promotions']);
	}
}

function fn_user_price_get_order_items_info_post(&$order, $v, $k)
{
	$user_prices = fn_get_product_user_price($v['product_id'], $order['user_id']);
	if (!empty($user_prices)) {
		$order['products'][$k]['original_price'] = $user_prices[0]['price'];
	}
}

function fn_user_price_get_product_price_post($product_id, $amount, $auth, &$price) {
	$user_prices = fn_get_product_user_price($product_id);
	if (!empty($user_prices)) {
		$price = $user_prices[0]['price'];
	}
}
//	[/HOOKs]

function fn_update_product_user_price($product_id, $user_prices, $delete_price = true)
{
	//	delete old data
	if ($delete_price) db_query("DELETE FROM ?:user_price WHERE product_id = ?i", $product_id);

	foreach ($user_prices as $user_price) {
		if (empty($user_price['user_id'])) {
			continue;
		}
		if (isset($user_price['price']) && $user_price['price'] == '') {
			db_query("DELETE FROM ?:user_price WHERE product_id = ?i AND user_id = ?i", $product_id, $user_price['user_id']);
		} elseif (is_numeric($user_price['price'])) {
			db_query(
				"REPLACE INTO ?:user_price ?e",
				[
					'user_id' => $user_price['user_id'],
					'price' => $user_price['price'],
					'product_id' => $product_id
				]
			);
		}
	}
	return true;
}

function fn_get_product_user_price($product_id, $user_id = 0)
{
	$condition = '';

	//	only for current user
	if (AREA == 'C') {
		if (!empty(Tygh::$app['session']['auth']['user_id'])) {
			$condition = db_quote(" AND user_id = ?i", Tygh::$app['session']['auth']['user_id']);
		} else {
			//	only for signed users
			return null;
		}
	}

	if ($user_id) {
		$condition = db_quote(" AND user_id = ?i", $user_id);
	}

	$product_id = is_array($product_id) ? $product_id : (Array) $product_id;
	$user_prices = db_get_array("SELECT * FROM ?:user_price WHERE product_id IN (?n) $condition", $product_id);

	//	info for settings
	if (AREA == 'A') {
		fn_get_user_price_user_data($user_prices);
	}

	return $user_prices;
}

function fn_get_user_price_user_data(&$user_prices)
{
	$user_ids = fn_array_column($user_prices, 'user_id');
	$user_datas = db_get_hash_array("SELECT user_id, firstname, lastname, email FROM ?:users WHERE user_id IN (?n)", 'user_id', $user_ids);

	array_walk($user_prices, function(&$user_price) use ($user_datas) {
		$user_price['user_data'] = $user_datas[$user_price['user_id']] ?? '';
	});
}

function fn_user_price_delete_product_post($product_id, $product_deleted) {
	if ($product_deleted) db_query('DELETE FROM ?:user_price WHERE product_id = ?i', $product_id);
}

function fn_user_price_post_delete_user($user_id, $user_data, $result) {
	if ($result) db_query('DELETE FROM ?:user_price WHERE user_id = ?i', $user_id);
}