<?php

defined('BOOTSTRAP') or die('Access denied');

// [HOOKs]
function fn_sort_promotions_by_data_get_promotions($params, $fields, &$sortings, $condition, $join, $group, $lang_code)
{
    $sortings['from_date'] = '?:promotions.from_date';
    $sortings['to_date'] = '?:promotions.to_date';
}
// [/HOOKs]
