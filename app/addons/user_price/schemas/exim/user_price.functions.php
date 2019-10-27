<?php

function fn_import_user_price(&$primary_object_id, &$object, &$options, &$processed_data, &$processing_groups) {
	$name = trim($object['Name']);
	//$ug_id = db_get_field('SELECT usergroup_id FROM ?:usergroup_descriptions WHERE usergroup = ?s AND lang_code = ?s', $name, $object['lang_code']);
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
		//get user_id by function!!
		$price = array(
			'user_id' => db_get_field('SELECT user_id FROM ?:users WHERE firstname LIKE ?l OR lastname LIKE ?l', $like_name, $like_name),
			'price' => $object['price'],
		);
		if ($price['user_id']) {
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