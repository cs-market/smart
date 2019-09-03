<?php

function fn_vendor_promotions_get_promotion_data_pre($promotion_id, &$extra_condition, $lang_code) {
	$extra_condition .= fn_get_company_condition('p.company_id');
}