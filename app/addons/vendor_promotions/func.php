<?php

use Tygh\Registry;

function fn_vendor_promotions_get_promotion_data_pre($promotion_id, &$extra_condition, $lang_code) {
	$extra_condition .= fn_get_company_condition('p.company_id');
}

function fn_vendor_promotions_update_promotion_pre(&$data, $promotion_id, $lang_code) {
	if (Registry::get('runtime.company_id')) {
		$data['company_id'] = Registry::get('runtime.company_id');
	}
}