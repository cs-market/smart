<?php
/*****************************************************************************
*                                                                            *
*                   All rights reserved! eCom Labs LLC                       *
* http://www.ecom-labs.com/about-us/ecom-labs-modules-license-agreement.html *
*                                                                            *
*****************************************************************************/

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_ecl_search_improvements_additional_fields_in_search(&$params, $fields, $sortings, $condition, &$join, $sorting, $group_by, &$tmp, $piece, $having)
{
	$search_in = Registry::get('addons.ecl_search_improvements.multiple_checkboxes');
    if (AREA == 'C' && !empty($params['pcode']) && $params['pcode'] == 'Y' && (empty($search_in) || !empty($search_in['pcode']))) {
        $params['pcode'] = '';
        $tmp .= db_quote(" OR products.product_code LIKE ?l", '%' . $piece . '%');
        if (strpos($join, 'inventory2') === false) {
            $join .= " LEFT JOIN ?:product_options_inventory as inventory2 ON inventory2.product_id = products.product_id";
        }

        $tmp .= db_quote(" OR inventory2.product_code LIKE ?l", '%' . $piece . '%');

        if (Registry::get('addons.product_variations.status') == 'A') {
            if (strpos($join, 'variations2') === false) {
                $join .= " LEFT JOIN ?:products as variations2 ON products.product_id = variations2.parent_product_id";
            }
            $tmp .= db_quote(" OR variations2.product_code LIKE ?l", '%' . $piece . '%');
        }
    } elseif (AREA == 'C' && !empty($params['pcode']) && $params['pcode'] == 'N' && empty($search_in['pcode'])) {
		$params['pcode'] = '';
	}
    $option = Registry::get('addons.ecl_search_improvements.multiple_checkboxes');
    if (fn_allowed_for('MULTIVENDOR') && (empty($option) || !empty($option['vendor']) || isset($option['N']))) {
        $tmp .= db_quote(" OR companies.company LIKE ?l", '%' . $piece . '%');
        if (!in_array('companies', $params['extend'])) {
            $params['extend'][] = 'companies';
        }
    }
}

function fn_ecl_search_improvements_get_users($params, $fields, $sortings, &$condition, $join, $auth)
{
    if (!empty($params['phone'])) {
        $condition['phone'] = db_quote(" AND (?:users.phone LIKE ?l OR ?:user_profiles.b_phone LIKE ?l OR ?:user_profiles.s_phone LIKE ?l)", "%".trim($params['phone'])."%", "%".trim($params['phone'])."%", "%".trim($params['phone'])."%");
    }
}

function fn_get_search_words($params = array(), $items_per_page = 0, $lang_code = CART_LANGUAGE)
{
    $default_params = array(
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    if (is_array($params)) {
        $params = array_merge($default_params, $params);
    } else {
        $params = $default_params;
    }
    
    $sortings = array (
        'key_word' => '?:search_key_words.key_word',
        'timestamp' => '?:search_key_words.timestamp',
        'popularity' => '?:search_key_words.popularity',
    );
    
    $sorting = db_sort($params, $sortings, 'popularity', 'desc');
    $limit = $condition = '';
    
    $condition .= fn_get_company_condition('?:search_key_words.company_id');

    if (!empty($params['period']) && $params['period'] != 'A') {
        list($params['time_from'], $params['time_to']) = fn_create_periods($params);

        $condition .= db_quote(" AND (?:search_key_words.timestamp >= ?i AND ?:search_key_words.timestamp <= ?i)", $params['time_from'], $params['time_to']);
    }

    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(DISTINCT(?:search_key_words.key_word_id)) FROM ?:search_key_words WHERE lang_code = ?s ?p ?p", DESCR_SL, $condition, $sorting);
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }
    
    $key_words = db_get_array("SELECT * FROM ?:search_key_words WHERE lang_code = ?s ?p ?p ?p", DESCR_SL, $condition, $sorting, $limit);
    
    return array($params, $key_words);
}

function fn_ecl_search_improvements_get_orders($params, $fields, $sortings, &$condition, $join, $group)
{
    if (!empty($params['phone'])) {
        $condition .= db_quote(" AND (?:orders.phone LIKE ?l OR ?:orders.b_phone LIKE ?l OR ?:orders.s_phone LIKE ?l)", '%' . $params['phone'] . '%', '%' . $params['phone'] . '%', '%' . $params['phone'] . '%');
    }
}

function fn_reset_search_words()
{
    $company_id = Registry::get('runtime.company_id');

    $where = '';
    if (!empty($company_id)) {
        $where .= db_quote(" WHERE company_id = ?i ", $company_id);
    }
    db_query("DELETE FROM ?:search_key_words $where");
    return true;
}

function fn_ecl_search_improvements_get_search_objects_post($schema, $area, &$search)
{
    $search['action_links']['products'] = str_replace('=any&', '=' . Registry::get('addons.ecl_search_improvements.admin_search_type') . '&', $search['action_links']['products']);
}