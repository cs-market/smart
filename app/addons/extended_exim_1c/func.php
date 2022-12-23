<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_extended_exim_1c_get_categories(&$params, $join, &$condition, $fields, $group_by, $sortings, $lang_code) {
    if (isset($params['search_query']) && !fn_is_empty($params['search_query'])) {
        $search = db_quote(' AND ?:category_descriptions.category LIKE ?l', '%' . trim($params['search_query']) . '%');
        $condition = str_replace($search, '', $condition);
        $condition .= db_quote(' AND (?:category_descriptions.category LIKE ?l OR ?:category_descriptions.alternative_names LIKE ?l)', '%' . trim($params['search_query']) . '%', '%' . trim($params['search_query']) . '%');
    }
    if (in_array(Registry::get('runtime.controller'), ['sd_exim_1c', 'exim_1c', 'commerceml'])) {
        $remove = " AND (" . fn_find_array_in_set(Tygh::$app['session']['auth']['usergroup_ids'], '?:categories.usergroup_ids', true) . ")";
        $condition = str_replace($remove, '', $condition);
    }
}
