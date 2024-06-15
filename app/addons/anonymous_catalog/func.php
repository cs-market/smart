<?php

use Tygh\Registry;

defined('AREA') or die('Access denied');

function fn_anonymous_catalog_get_products($params, $fields, $sortings, &$condition, &$join, $sorting, $group_by, $lang_code, $having) {
    $auth = Tygh::$app['session']['auth'];

    if (Registry::get('runtime.mode') != 'product_catalog' || $params['area'] != 'C' || $auth['user_id']) return;

    $search_condition = ' AND (' . fn_find_array_in_set($auth['usergroup_ids'], 'products.usergroup_ids', true) . ')';
    $add_condition = ' AND (' . fn_find_array_in_set(explode(',',Registry::get('addons.anonymous_catalog.anonymous_usergroups')), 'products.usergroup_ids', true) . ')';
    $condition = str_replace($search_condition, $add_condition, $condition);


    $search_join .= ' AND (' . fn_find_array_in_set(Tygh::$app['session']['auth']['usergroup_ids'], '?:categories.usergroup_ids', true) . ')';
    $add_join .= ' AND (' . fn_find_array_in_set(explode(',',Registry::get('addons.anonymous_catalog.anonymous_usergroups')), '?:categories.usergroup_ids', true) . ')';

    $join = str_replace($search_join, $add_join, $join);
}

function fn_anonymous_catalog_get_product_filters($params = array(), $items_per_page = 0, $lang_code = DESCR_SL) {
    list($filters) = fn_get_product_filters($params, $items_per_page, $lang_code);
    if (!empty($params['features_hash']) && empty($params['skip_advanced_variants'])) {
        $selected_filters = fn_parse_filters_hash($params['features_hash']);
    }

    foreach ($filters as $filter_id => $filter) {
        if (!empty($filters[$filter_id]['variants'])) {
            // Select variants
            if (!empty($selected_filters[$filter_id])) {
                foreach ($selected_filters[$filter_id] as $variant_id) {
                    if (!empty($filters[$filter_id]['variants'][$variant_id])) {
                        $filters[$filter_id]['variants'][$variant_id]['selected'] = true;
                        $filters[$filter_id]['selected_variants'][$variant_id] = $filters[$filter_id]['variants'][$variant_id];
                    }
                }
            }
        }
    }

    return [$filters];
}

function fn_anonymous_catalog_get_product_data($product_id, $field_list, &$join, $auth, $lang_code, $condition, &$price_usergroup) {
    if (empty($auth['user_id'])) {
        $owner_of_product = fn_get_company_by_product_id($product_id);
        if ($owner_of_product['company_id'] == 45) {
            $avail_cond = db_quote("?p", ' AND (' . fn_find_array_in_set([0,1], "?:categories.usergroup_ids", true) . ')');
            $join = str_replace($avail_cond, '', $join);

            $price_usergroup = '';
        }
    }
}
