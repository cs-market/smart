<?php

use Tygh\Registry;

function fn_get_conditions($conditions, &$promo_extra) {
	
	foreach ($conditions as $condition) {
		if (isset($condition['conditions'])) {
			fn_get_conditions($condition['conditions'], $promo_extra);
		} elseif (isset($condition['condition']) && in_array($condition['condition'], array('products', 'usergroup'))) {
			if (is_array($condition['value'])) {
				foreach ($condition['value'] as $value) {
					$promo_extra[$condition['condition']][] = $value['product_id'];
				}
			} else {
				$promo_extra[$condition['condition']][] = $condition['value'];
			}
		}
	}
}

function fn_category_promotion_update_promotion_post($data, $promotion_id, $lang_code) {
	$conditions = unserialize($data['conditions']);
	$products = array();
	if (isset($conditions['conditions'])) {
		fn_get_conditions($conditions['conditions'], $promo_extra);
	}
	$promo_extra = array_map(function($arr) {return  implode(',', $arr);}, $promo_extra);

	if (!empty($promo_extra)) {
		db_query('UPDATE ?:promotions SET ?u WHERE promotion_id = ?i', $promo_extra, $promotion_id);
	}
}

function fn_category_promotion_get_products_before_select(&$params, $join, &$condition, $u_condition, $inventory_join_cond, $sortings, $total, $items_per_page, $lang_code, $having){
	if (AREA != 'A') {
		if (!empty($params['cid'])) {
			if (in_array(
				$params['category_id'],
				explode(',', Registry::get('addons.category_promotion.category_ids'))
			)) {
				$promo_condition = db_quote(
					' AND IF(from_date, from_date <= ?i, 1) AND IF(to_date, to_date >= ?i, 1) AND status IN (?a)',
					TIME,
					TIME,
					array('A', 'H')
				);
				$promo_condition .=' AND (' . fn_find_array_in_set(Tygh::$app['session']['auth']['usergroup_ids'], "usergroup", true) . ')';
				$data = db_get_fields("SELECT products FROM ?:promotions WHERE products != '' $promo_condition");
				$product_ids = array_unique(explode(',', implode(',', $data)));
			}
			if (!empty($product_ids)) {
				$cids = is_array($params['cid']) ? $params['cid'] : explode(',', $params['cid']);

				if (isset($params['subcats']) && $params['subcats'] == 'Y') {
					$_ids = db_get_fields(
						"SELECT a.category_id"."
						 FROM ?:categories as a"."
						 LEFT JOIN ?:categories as b"."
						 ON b.category_id IN (?n)"."
						 WHERE a.id_path LIKE CONCAT(b.id_path, '/%')",
						$cids
					);

					$cids = fn_array_merge($cids, $_ids, false);
				}

				$condition .= db_quote(" AND (?:categories.category_id IN (?n) OR products.product_id IN (?n))", $cids, $product_ids);
				unset($params['cid']);
			}
		}
	}
}