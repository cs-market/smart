<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_get_shops(&$params, $items_per_page, $area = AREA) {
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);
    
    //$condition .= fn_get_shop_condition('?:shops.shop_id');
    $sortings = array (
        'id' => '?:shops.shop_id',
        'shop' => '?:shops.shop',
        'date' => '?:shops.timestamp',
        'status' => '?:shops.status',
    );
    $condition = $join = $group = '';
    if (Registry::get('runtime.company_id')) {
        $condition .= db_quote(" AND company_id = ?i", Registry::get('runtime.company_id'));
    }
    
    if (!empty($params['shop_id'])) {
        if (!is_array($params['shop_id'])) {
            $params['shop_id'] = explode(',', $params['shop_id']);
        }
        $condition .= db_quote(' AND ?:shops.shop_id IN (?n)', $params['shop_id']);
    }

    $sorting = db_sort($params, $sortings, 'shop', 'asc');
    // Paginate search results
    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(DISTINCT(?:shops.shop_id)) FROM ?:shops $join WHERE 1 $condition");
        $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
    }

    $shops = db_get_array("SELECT * FROM ?:shops $join WHERE 1 $condition $group $sorting $limit");

    return array($shops, $params);
}

function fn_update_shop($shop_data, $shop_id = 0) {
    unset($shop_data['shop_id']);

    if (empty($shop_id)) {
        $shop_id = db_query("INSERT INTO ?:shops ?e", $shop_data);
    } else {
        db_query("UPDATE ?:shops SET ?u WHERE shop_id = ?i", $shop_data, $shop_id);
    }

    return $shop_id;
}


// function fn_get_company_condition($db_field = 'shop_id', $add_and = true, $shop_id = '', $show_admin = false, $force_condition_for_area_c = false)
// {
//     if (fn_allowed_for('ULTIMATE')) {
//         // Completely remove company condition for sharing objects

//         static $sharing_schema;

//         if (empty($sharing_schema) && Registry::get('addons_initiated') === true) {
//             $sharing_schema = fn_get_schema('sharing', 'schema');
//         }

//         // Check if table was passed
//         if (strpos($db_field, '.')) {
//             list($table, $field) = explode('.', $db_field);
//             $table = str_replace('?:', '', $table);

//             // Check if the db_field table is in the schema
//             if (isset($sharing_schema[$table])) {
//                 return '';
//             }

//         } else {
//             return '';
//         }

//         if (Registry::get('runtime.company_id') && !$company_id) {
//             $company_id = Registry::get('runtime.company_id');
//         }
//     }

//     if ($company_id === '') {
//         $company_id = Registry::ifGet('runtime.company_id', '');
//     }

//     $skip_cond = AREA == 'C' && !$force_condition_for_area_c && !fn_allowed_for('ULTIMATE');

//     if (!$company_id || $skip_cond) {
//         $cond = '';
//     } else {
//         $cond = $add_and ? ' AND' : '';
//         // FIXME 2tl show admin
//         if ($show_admin && $company_id) {
//             $cond .= db_quote(" $db_field IN (0, ?i)", $company_id);
//         } else {
//             $cond .= db_quote(" $db_field = ?i", $company_id);
//         }
//     }

//     /**
//      * Hook for changing result of function
//      *
//      * @param string $db_field                   Field name (usually table_name.company_id)
//      * @param bool   $add_and                    Include or not AND keyword berofe condition.
//      * @param mixed  $company_id                 Company ID for using in SQL condition.
//      * @param bool   $show_admin                 Include or not company_id == 0 in condition (used in the
//      *                                           MultiVendor Edition)
//      * @param bool   $force_condition_for_area_c Used in the MultiVendor Edition. By default, SQL codition should be
//      *                                           empty in the customer area. But in some cases, this condition should
//      *                                           be enabled in the customer area. If <i>$force_condition_for_area_c</i>
//      *                                           is set, condition will be formed for the customer area.
//      * @param string $cond                       Final condition
//      */
//     fn_set_hook(
//         'get_company_condition_post',
//         $db_field,
//         $add_and,
//         $company_id,
//         $show_admin,
//         $force_condition_for_area_c,
//         $cond
//     );

//     return $cond;
// }
function fn_multishop_get_categories($params, $join, &$condition, $fields, $group_by, $sortings, $lang_code) {
//     $auth = & Tygh::$app['session']['auth'];
//     if (AREA == 'C' && !$auth['user_id']) {
//         $remove_condition = " AND (" . fn_find_array_in_set($auth['usergroup_ids'], '?:categories.usergroup_ids', true) . ")";
//         $condition = str_replace($remove_condition, '', $condition);
//         $condition .= " AND (" . fn_find_array_in_set(Registry::get('runtime.shop_usergroups'), '?:categories.usergroup_ids', true) . ")";
//     }
}

function fn_multishop_get_category_data($category_id, $field_list, $join, $lang_code, $conditions) {
    //fn_print_die($category_id, $field_list, $join, $lang_code, $conditions);
}

function fn_multishop_dispatch_assign_template($controller, $mode, $area, &$controllers_cascade)
{
    if ($area == 'A' && fn_check_object_exists_for_root($controller, $mode)) {
        // Do not run current controller now
        foreach ($controllers_cascade as $idx => $file) {
            list($name) = explode('.', fn_basename($file)); // get all pre/post controllers here
            if ($name == $controller) {
                unset($controllers_cascade[$idx]);
            }
        }

        $view = Tygh::$app['view'];
        $view->assign('content_tpl', 'common/select_company.tpl');
        $view->assign('select_id', 'vendor_selector');

        $schema = fn_get_permissions_schema('admin');

        if (isset($schema[$controller]['modes'][$mode]['page_title'])) {
            $view->assign('title', $schema[$controller]['modes'][$mode]['page_title']);

        } elseif (isset($schema[$controller]['page_title'])) {
            $view->assign('title', $schema[$controller]['page_title']);
        }
    }
}

function fn_multishop_layout_get_default($_this, $theme_name, &$condition, $fields, $join) {
//     if (Registry::get('runtime.shop_id')) {
//         $condition .= db_quote(' AND shop_id = ?i', Registry::get('runtime.shop_id'));
//     }
}

function fn_multishop_layout_get_list($_this, $params, $condition, $fields, $join) {
    //if (Registry::get('runtime.shop_id')) $condition .= db_quote(' AND shop_id = ?i', Registry::get('runtime.shop_id'));
}

function fn_multishop_layout_update_pre($_this, $layout_id, &$layout_data, $create) {
//     if (Registry::get('runtime.shop_id')) {
//         $layout_data['shop_id'] = Registry::get('runtime.shop_id');
//     }
}

function fn_multishop_get_theme_path_pre(&$path, $area, $company_id, $theme_names) {
//     if (Registry::get('runtime.shop_id') && $area == 'C') {
//         $path = str_replace('[theme]', '{theme}', $path);
//     }

}
function fn_multishop_get_theme_path(&$path, $area, $dir_design, $company_id) {
//     if (Registry::get('runtime.shop_id') && $area == 'C') {
//         $theme_name = db_get_field('SELECT theme_name FROM ?:shops WHERE shop_id = ?i', Registry::get('runtime.shop_id'));
//         $path = str_replace('{theme}', $theme_name, $path);
//     }
}

function fn_check_object_exists_for_root($controller = '', $mode = '')
{
    $schema = fn_get_permissions_schema('admin');

    $controller = empty($controller) ? Registry::get('runtime.controller') : $controller;
    $mode = empty($mode) ? Registry::get('runtime.mode') : $mode;

    $vendor_only = false;

    if (!Registry::get('runtime.company_id')) {
        if (isset($schema[$controller]['modes'][$mode]['vendor_only'])) {
            $vendor_only = $schema[$controller]['modes'][$mode]['vendor_only'];
        } elseif (isset($schema[$controller]['vendor_only']) && is_array($schema[$controller]['vendor_only']['display_condition']) && !empty($schema[$controller]['vendor_only']['display_condition'])) {
            $vendor_only = fn_ult_check_display_condition($_REQUEST, $schema[$controller]['vendor_only']['display_condition']);
        } elseif (isset($schema[$controller]['vendor_only']) && $schema[$controller]['vendor_only'] == true) {
            $vendor_only = $schema[$controller]['vendor_only'];
        }
    }

    return $vendor_only;
}

function fn_get_shop_name($shop_id) {
    if ($shop_id) {
        return db_get_field('SELECT shop FROM ?:shops WHERE shop_id = ?i', $shop_id);
    }
}
