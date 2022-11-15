<?php

if ( !defined('AREA') ) { die('Access denied'); }

function fn_promotion_import_maintenance_promotion_check_existence(&$promotion_ids, $cart, $auth) {
    if (!empty($promotion_ids)) {
        $external_ids = db_get_fields('SELECT external_id FROM ?:promotions WHERE promotion_id IN (?a)', $promotion_ids);
        $externals = [];
        foreach ($external_ids as $value) {
            list($value) = explode('.', $value);
            $cond = db_quote('external_id LIKE ?l', $value . '%');
            $externals[$value] = $cond;
        }
        $external_condition = "AND (" . implode(' OR ', $externals) . ")";

        $promotion_ids = db_get_fields("SELECT promotion_id FROM ?:promotions WHERE 1 $external_condition");
    }
}
