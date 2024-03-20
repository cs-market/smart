<?php

use Tygh\Registry;

defined('AREA') or die('Access denied');

function fn_anonymous_catalog_get_products($params, $fields, $sortings, &$condition, $join, $sorting, $group_by, $lang_code, $having) {
    $auth = Tygh::$app['session']['auth'];

    if (Registry::get('runtime.mode') != 'product_catalog' || $params['area'] != 'C' || $auth['user_id']) return;

    $search_condition = ' AND (' . fn_find_array_in_set($auth['usergroup_ids'], 'products.usergroup_ids', true) . ')';

    $add_condition = ' AND (' . fn_find_array_in_set(explode(',',Registry::get('addons.anonymous_catalog.anonymous_usergroups')), 'products.usergroup_ids', true) . ')';
    $condition = str_replace($search_condition, $add_condition, $condition);
}
