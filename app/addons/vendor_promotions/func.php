<?php

function fn_vendor_promotions_get_promotion_data_pre($promotion_id, &$extra_condition, $lang_code) {
	$extra_condition .= fn_get_company_condition('p.company_id');
}

function fn_vendor_promotions_get_promotions($params, $fields, $sortings, &$condition, $join, $group, $lang_code) {
	$auth = $_SESSION['auth'];
	$ug_condition = fn_find_array_in_set($auth['usergroup_ids'], 'vc.usergroup_ids', false);
	$company_ids = db_get_fields("SELECT company_id FROM ?:companies AS c LEFT JOIN ?:vendor_plans AS vc ON c.plan_id = vc.plan_id WHERE $ug_condition" );
	if (!empty($company_ids)) {
		$condition .= db_quote(' AND (company_id IN (?a) OR company_id = 0) ', $company_ids);
	}
}