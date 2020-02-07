<?php

function fn_import_user_price(&$primary_object_id, &$object, &$options, &$processed_data, &$processing_groups) {
	$name = trim($object['Name']);
	if ($ug_id = db_get_field('SELECT usergroup_id FROM ?:usergroup_descriptions WHERE usergroup = ?s AND lang_code = ?s', $name, $object['lang_code'])) {
		$price = array(
			'product_id' => $primary_object_id['product_id'],
			'price' => $object['price'],
			'usergroup_id' => $ug_id,
		);
		if(db_query("REPLACE INTO ?:product_prices ?e", $price)) {
			$processed_data['E'] += 1;
		}
		//process_qty_discounts
	} else {
		$like_name = '%' . $name . '%';
		//TODO make fn_get_users more smart!
		list($users, ) = fn_get_users(array('user_login' => $name));
		if (!empty($users)) {
			$user = array_shift($users);
		} else {
			list($users, ) = fn_get_users(array('name' => $name));
			$user = array_shift($users);
		}
		if (!empty($user)) {
			$price = array(
				'user_id' => $user['user_id'],
				'price' => $object['price'],
			);
			//process_user_prices
			if (fn_update_product_user_price($primary_object_id['product_id'], array($price), false)) {
				$processed_data['E'] += 1;
			}
		} else {
			// skip record
			$processed_data['S'] += 1;
		}
	}
}