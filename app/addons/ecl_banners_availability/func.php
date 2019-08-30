<?php
/*****************************************************************************
*                                                                            *
*                   All rights reserved! eCom Labs LLC                       *
* http://www.ecom-labs.com/about-us/ecom-labs-modules-license-agreement.html *
*                                                                            *
*****************************************************************************/

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_ecl_banners_availability_get_banner_data($banner_id, $lang_code, &$fields, $joins, $condition)
{
    $fields[] = '?:banners.from_date';
    $fields[] = '?:banners.to_date';
    $fields[] = '?:banners.usergroup_ids';
}

function fn_ecl_banners_availability_get_banners(&$params, &$condition, &$sorting, $limit, $lang_code)
{
    if (AREA == 'C') {
        $condition .= db_quote(" AND IF(?:banners.from_date, ?:banners.from_date <= ?i, 1) AND IF(?:banners.to_date, ?:banners.to_date >= ?i, 1) ", TIME, TIME);

        $auth = $_SESSION['auth'];
        $condition .= db_quote(" AND (" . fn_find_array_in_set($auth['usergroup_ids'], '?:banners.usergroup_ids', true) . ")");
    
        if (isset($params['block_data']) && !empty($params['block_data']['content']) && !empty($params['block_data']['content']['items']) && $params['block_data']['content']['items']['filling'] == 'random_banners') {
            $sorting = ' ORDER BY RAND() ';
            if (isset($params['item_ids'])) {
                unset($params['item_ids']);
            }
        }
    }
}