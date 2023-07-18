<?php

use Tygh\Registry;
use Tygh\Models\Vendor;

defined('BOOTSTRAP') or die('Access denied');

function fn_vendor_usergroups_get_usergroups($params, $lang_code, $field_list, $join, &$condition, $group_by, $order_by, $limit) {
    if (!isset($params['usergroup_id']))
    $condition .= fn_vendor_usergroups_get_company_usergroup_condition(isset($params['company_id']) ? $params['company_id'] : null);
    
}

function fn_vendor_usergroups_get_simple_usergroups($type, $get_default, $lang_code, &$where) {
    $where .= fn_vendor_usergroups_get_company_usergroup_condition();
}

function fn_vendor_usergroups_get_company_usergroup_condition($company_id = null) {
    $condition = '';
    $company_id = $company_id ?? Registry::get('runtime.company_id');

    if (!empty($company_id)) {
        $company = Vendor::model()->find($company_id);

        if (!empty($company) && !empty($company->usergroups)) {
            $condition = db_quote(' AND a.usergroup_id IN (?a)', $company->usergroups);
        }
    }

    return $condition;
}

function fn_vendor_usergroups_update_usergroup($usergroup_data, $usergroup_id, $create) {
    if ($create && !empty(Registry::get('runtime.company_id')) && $plan_id = db_get_field('SELECT plan_id FROM ?:companies WHERE company_id = ?i', Registry::get('runtime.company_id'))) {
        db_query("UPDATE ?:vendor_plans SET usergroups = ?p WHERE plan_id = ?i", fn_add_to_set('usergroups', $usergroup_id), $plan_id);
    }
}

function fn_vendor_usergroups_delete_usergroups($usergroup_ids) {
    foreach ($usergroup_ids as $usergroup_id) db_query("UPDATE ?:vendor_plans SET usergroups = ?p", fn_remove_from_set('usergroups', $usergroup_id));
}

function fn_vendor_usergroups_get_default_usergroups(&$default_usergroups, $lang_code) {
    if (Registry::get('runtime.company_id')) {
        $default_usergroups = array();
    }
}

function fn_vendor_usergroups_update_product_pre(&$product_data, $product_id, $lang_code, $can_update) {
    if ($cid = (isset($product_data['company_id'])) ? $product_data['company_id'] : db_get_field('SELECT company_id FROM ?:products WHERE product_id = ?i', $product_id)) {
        $company = Vendor::model()->find($cid);
        if (!empty($company) && !empty($company->usergroups)) {
            if (!$product_id && !$product_data['usergroup_ids']) {
                $product_data['usergroup_ids'] = $company->usergroups;
            } elseif (isset($product_data['usergroup_ids'])) {
                $product_data['usergroup_ids'] = array_intersect($product_data['usergroup_ids'], $company->usergroups);
            }
        }
    }
}

function fn_vendor_usergroups_update_category_pre(&$category_data, $category_id, $lang_code) {
    if (isset($_REQUEST['preset_id']) && !$category_id) {
        list($presets) = fn_get_import_presets(array(
            'preset_id' => $_REQUEST['preset_id'],
        ));
        $preset = reset($presets);
        if ($preset['company_id']) {
            if ($company = Vendor::model()->find($preset['company_id'])) {
                $category_data['usergroup_ids'] = $company->usergroups;
            }
        }
        $category_data['add_category_to_vendor_plan'] = $company->plan_id;
    }
}

function fn_vendor_usergroups_update_category_post($category_data, $category_id, $lang_code) {
    if (!empty($category_data['add_category_to_vendor_plan'])) {
        db_query("UPDATE ?:vendor_plans SET categories = ?p  WHERE plan_id = ?i", fn_add_to_set('categories', $category_id), $category_data['add_category_to_vendor_plan']);
    }
}

function fn_vendor_usergroups_vendor_plan_before_save(&$_this, $result) {
    if (version_compare(PRODUCT_VERSION, '4.13.1', '<')) {
        if (empty($_this->usergroups)) {
            $_this->usergroup_ids = [];
        } elseif (is_array($_this->usergroups)) {
            $_this->usergroup_ids = $_this->usergroups;
            $_this->usergroups = implode(',', $_this->usergroups);
        } elseif (is_string($_this->usergroups)) {
            $_this->usergroup_ids = array_map('intval', explode(',', $_this->usergroups));
        }
    }
}

function fn_vendor_usergroups_category_promotion_get_products_before_select(&$params) {
    if ($usergroup_ids = db_get_field('SELECT usergroup_ids FROM ?:categories WHERE category_id = ?i', $params['cid'])) {
        $ug_condition = fn_find_array_in_set(explode(',', $usergroup_ids), 'usergroups', true);
        if ($company_id = db_get_field("SELECT company_id FROM ?:companies AS c LEFT JOIN ?:vendor_plans AS vc ON c.plan_id = vc.plan_id WHERE $ug_condition")) {
            $params['company_id'] = $company_id;
        }
    }
}
