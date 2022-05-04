<?php

use Tygh\Registry;
use Tygh\Models\Company;
use Tygh\Enum\ProductFeatures;

function fn_exim_smart_distribution_import_images($prefix, $image_file, $detailed_file, $position, $type, $object_id, $object, $import_options = null)
{
    if ($detailed_file && strpos($detailed_file, '://') !== false) {
        $extensions = [
            'jpg', 'jpeg', 'png', 'bmp'
        ];

        $img_url = false;
        foreach ($extensions as $extension) {
            if (stripos($detailed_file, $extensions) !== false) {
                $img_url = true;
                break;
            }
        }

        if (!$img_url) {
            //  if return <img />, get src
            $img_tag = fn_get_contents($detailed_file);
            preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $img_tag, $matches);
            if (isset($matches[1])) {
                $detailed_file = $matches[1];
            }
        }
    }

    fn_exim_import_images($prefix, $image_file, $detailed_file, $position, $type, $object_id, $object, $import_options);
}

function fn_exim_sd_set_product_categories(
    $product_id,
    $link_type,
    array $categories_data,
    $category_delimiter,
    $store_name = '',
    array &$processed_data = [],
    $is_new = false,
    $vendor_name = ''
)
{
    if (fn_is_empty($categories_data) && $link_type === 'A') {
        return false;
    }

    $company_id = 0;
    if (fn_allowed_for('MULTIVENDOR')) {
        $store_name = trim($vendor_name);
    }
    if (Registry::get('runtime.company_id')) {
        $company_id = Registry::get('runtime.company_id');
    } else {
        $company_id = fn_get_company_id_by_name($store_name);

        if (!$company_id) {
            $company_data = ['company' => $store_name, 'email' => ''];
            $company_id = fn_update_company($company_data, 0);
        }
    }

    // Sets a default category
    if (fn_is_empty($categories_data)) {
        // If the category data is empty and the product exists, keep the existing product category
        if (!$is_new) {
            return false;
        }

        if (isset($processed_data['default_categories']['ids'][$company_id])) {
            $default_category_id = $processed_data['default_categories']['ids'][$company_id];
        } else {
            $default_category_id = fn_get_or_create_default_category_id($company_id);
            $processed_data['default_categories']['ids'][$company_id] = $default_category_id;
        }

        $default_category_id = (int) $default_category_id;
        $processed_data['default_categories']['used'][] = $default_category_id;

        fn_exim_set_product_category([
            'product_id'  => $product_id,
            'category_id' => $default_category_id,
            'link_type'   => 'M',
        ]);

        fn_update_product_count([$default_category_id]);

        return true;
    }

    $set_delimiter = ';';
    if (fn_allowed_for('ULTIMATE')) {
        $store_delimiter = ':';
        $paths_store = array();
    }

    $paths = array();
    $updated_categories = array();

    foreach ($categories_data as $lang => $data) {
        $_paths = str_getcsv($data, $set_delimiter, "'");
        array_walk($_paths, 'fn_trim_helper');

        foreach ($_paths as $k => $cat_path) {
            if (fn_allowed_for('ULTIMATE') && strpos($cat_path, $store_delimiter)) {
                $cat_path = str_getcsv($cat_path, $store_delimiter, '|');

                if (count($cat_path) > 1) {
                    $paths_store[$k] = reset($cat_path);
                    $cat_path = $cat_path[1];
                } else {
                    $cat_path = reset($cat_path);
                }
            }

            $category = (strpos($cat_path, $category_delimiter) !== false) ? explode($category_delimiter, $cat_path) : array($cat_path);
            foreach ($category as $key_cat => $cat) {
                $paths[$k][$key_cat][$lang] = $cat;
            }
        }
    }

    if (!fn_is_empty($paths)) {
        $category_condition = '';
        $joins = '';
        $select = '?:products_categories.*';
        if (fn_allowed_for('ULTIMATE')) {
            $joins = ' JOIN ?:categories ON ?:categories.category_id = ?:products_categories.category_id ';
            $category_condition = fn_get_company_condition('?:categories.company_id');
            $select .= ', ?:categories.category_id, ?:categories.company_id';
        }

        $main_category_id = db_get_field(
            'SELECT category_id FROM ?:products_categories WHERE product_id = ?i AND link_type = ?s',
            $product_id, 'M'
        );

        $cat_ids = array();
        $old_data = db_get_hash_array("SELECT $select FROM ?:products_categories $joins WHERE product_id = ?i AND link_type = ?s $category_condition", 'category_id', $product_id, $link_type);
        foreach ($old_data as $k => $v) {
            if ($v['link_type'] == $link_type) {
                $updated_categories[] = $k;
            }
            $cat_ids[] = $v['category_id'];
        }
        if (!empty($cat_ids)) {
            db_query("DELETE FROM ?:products_categories WHERE product_id = ?i AND category_id IN (?n)", $product_id, $cat_ids);
        }
    }

    foreach ($paths as $key_path => $categories) {

        if (!empty($categories)) {
            $parent_id = '0';

            foreach ($categories as $cat) {
                $category_condition = '';
                if (fn_allowed_for('ULTIMATE')) {
                    if (!empty($paths_store[$key_path]) && !Registry::get('runtime.company_id')) {
                        $path_company_id = fn_get_company_id_by_name($paths_store[$key_path]);
                        $category_condition = fn_get_company_condition('?:categories.company_id', true, $path_company_id);
                    } else {
                        $category_condition = fn_get_company_condition('?:categories.company_id', true, $company_id);
                    }
                } elseif (fn_allowed_for('MULTIVENDOR')) {
                    $company = Company::model()->find($company_id);
                    $plan_categories = ($company->categories) ? explode(',', $company->categories) : array();
                    if ($plan_categories) {
                        $category_condition = db_quote(' AND ?:categories.category_id IN (?a)', $plan_categories);
                    }

                }

                reset($cat);
                $main_lang = key($cat);
                $main_cat = array_shift($cat);

                $category_id = db_get_field(
                    'SELECT ?:categories.category_id FROM ?:category_descriptions '
                    . "INNER JOIN ?:categories ON ?:categories.category_id = ?:category_descriptions.category_id $category_condition "
                    . 'WHERE ?:category_descriptions.category = ?s AND lang_code = ?s AND parent_id = ?i',
                    $main_cat,
                    $main_lang,
                    $parent_id
                );

                if (!empty($category_id)) {
                    $parent_id = $category_id;
                    // [cs-market]
                    fn_set_hook('set_product_categories_exist', $category_id);
                } else {

                    $category_data = array(
                        'parent_id' => $parent_id,
                        'category' =>  $main_cat,
                        'timestamp' => TIME,
                    );

                    if (fn_allowed_for('ULTIMATE')) {
                        $category_data['company_id'] = !empty($path_company_id) ? $path_company_id : $company_id;
                    }

                    $category_id = fn_update_category($category_data);

                    foreach ($cat as $lang => $cat_data) {
                        $category_data = array(
                            'parent_id' => $parent_id,
                            'category' => $cat_data,
                            'timestamp' => TIME,
                        );

                        $category_data['company_id'] = $company_id;

                        fn_update_category($category_data, $category_id, $lang);
                    }

                    $parent_id = $category_id;
                }

            }

            $data = array(
                'product_id' => $product_id,
                'category_id' => $category_id,
                'link_type' => $link_type,
            );

            if (!empty($old_data) && !empty($old_data[$category_id])) {
                $data = fn_array_merge($old_data[$category_id], $data);
            }

            if (!empty($main_category_id) && $main_category_id == $category_id) {
                $data['link_type'] = 'M';
            }

            fn_exim_set_product_category($data);

            $updated_categories[] = $category_id;
        }
    }

    if (!empty($updated_categories)) {
        fn_update_product_count($updated_categories);

        return true;
    }

    return false;
}

function fn_sd_import_prepare_default_categories(array &$import_process_data, $import_data) {
    $default_categories = fn_get_all_default_categories_ids();

    // default category for all vendors
    if (!empty($default_categories[0])) {
        $company_ids = [];
        if (Registry::get('runtime.company_id')) {
            $company_ids[] = Registry::get('runtime.company_id');
        } else {
            $companies = array_unique(fn_array_column($import_data, 'company'));
            foreach ($companies as $name) {
                $company_ids[] = fn_get_company_id_by_name($name);
            }
        }
        foreach ($company_ids as $company_id) {
            $default_categories[$company_id] = $default_categories[0];
        }
    }

    $import_process_data['default_categories'] = [
        'ids' => $default_categories,
        'used_ids' => []
    ];
}

function fn_exim_set_add_product_usergroups($product_id, $data) {
    if (!empty($data)) {
        $old_usergroups = db_get_field("SELECT usergroup_ids FROM ?:products WHERE product_id = ?i", $product_id);
        $usergroup_ids = fn_exim_smart_distribution_get_usergroup_ids($data);

        if ($old_usergroups) {
            $usergoups = array_unique(
                array_merge(
                    explode(',', $old_usergroups),
                    $usergroup_ids
                )
            );
        } else {
            $usergoups = $usergroup_ids;
        }

        if (!empty($usergoups)) db_query("UPDATE ?:products SET usergroup_ids = ?s WHERE product_id = ?i", implode(',', $usergoups), $product_id);
    }
}

function fn_exim_smart_distribution_convert_usergroups($data) {
    if ($usergroup_ids = fn_exim_smart_distribution_get_usergroup_ids($data)) {
        $data = implode(',', $usergroup_ids);
    }

    return $data;
}

/**
 * Import product features with vendor
 *  based on the fn_exim_set_product_features
 *
 * @param int       $product_id         Product identifier
 * @param array     $data               Array of delimited lists of product features and their values
 * @param string    $features_delimiter Delimiter symbol
 * @param string    $lang_code          Language code
 * @param string    $store_name         Store name
 *
 * @return boolean Always true
 */
function fn_exim_smart_distribution_set_product_features($product_id, $data, $features_delimiter, $lang_code, $store_name = '')
{
    reset($data);
    $main_lang = key($data);
    $company_id = 0;
    $runtime_company_id = fn_get_runtime_company_id();
    $store_name = trim($store_name);
    if (fn_allowed_for('ULTIMATE')) {
        if ($runtime_company_id) {
            $company_id = $runtime_company_id;
        } else {
            $company_id = fn_get_company_id_by_name($store_name);
        }
    } elseif (fn_allowed_for('MULTIVENDOR')) {
        // [changed part]
        // get company_id to use with vendor
        if ($runtime_company_id) {
            $company_id = $runtime_company_id;
        } elseif ($store_name) {
            $company_id = fn_get_company_id_by_name($store_name);
        } else {
            $company_id = db_get_field("SELECT company_id FROM ?:products WHERE product_id = ?i", $product_id);
        }
        // [/changed part]
    }

    $features = fn_exim_parse_features($data, $features_delimiter);

    foreach ($features as $key => &$feature) {
        if (!empty($feature['group_name'])) {
            $feature_group = array(
                'name' => $feature['group_name'],
                'names' => $feature['group_names'],
                'type' => ProductFeatures::GROUP,
                'parent_id' => 0
            );

            $feature_group = fn_exim_smart_distribution_save_product_feature($feature_group, $company_id, $main_lang);

            if ($feature_group === false) {
                unset($features[$key]);
                continue;
            }

            $feature['parent_id'] = $feature_group['feature_id'];
        } else {
            $feature['parent_id'] = 0;
        }

        $feature = fn_exim_smart_distribution_save_product_feature($feature, $company_id, $main_lang);

        if ($feature === false) {
            unset($features[$key]);
            continue;
        }
    }
    unset($feature);

    if (!empty($features)) {
        fn_exim_save_product_features_values($product_id, $features, $main_lang);
    }

    return true;
}

/**
 * Save product feature with vendor
 *  based on the fn_exim_save_product_feature
 *
 * @param array     $feature    Product feature data
 * @param int       $company_id Company identifier
 * @param string    $lang_code  Language code
 *
 * @return array|bool
 */
function fn_exim_smart_distribution_save_product_feature(array $feature, $company_id, $lang_code)
{
    $allowed_feature_types = ProductFeatures::getAllTypes();
    $runtime_company_id = fn_get_runtime_company_id();

    if (strpos($allowed_feature_types, $feature['type']) === false && $feature['type'] != ProductFeatures::GROUP) {
        fn_set_notification('W', __('warning'), __('exim_error_incorrect_feature_type'));
        return false;
    }

    if (empty($feature['name'])) {
        fn_set_notification('W', __('warning'), __('exim_error_empty_feature_name'));
        return false;
    }

    // [changed part]
    $data = fn_exim_smart_distribution_find_feature($feature['name'], $feature['type'], $feature['parent_id'], $lang_code, $company_id);
    // [/changed part]

    if (fn_allowed_for('ULTIMATE') && empty($data) && !empty($company_id) && $runtime_company_id == 0) {
        $data = fn_exim_find_feature($feature['name'], $feature['type'], $feature['parent_id'], $lang_code, 0);

        if (!empty($data)) {
            fn_exim_update_share_feature($data['feature_id'], $company_id);
        }
    }

    if (empty($data)) {
        if (fn_allowed_for('MULTIVENDOR') && $runtime_company_id) {
            fn_set_notification('W', __('warning'), __('exim_vendor_cant_create_feature'));
            return false;
        }

        $data = array(
            'feature_id' => 0,
            'description' => $feature['name'],
            'feature_type' => $feature['type'],
            'lang_code' => $lang_code,
            'company_id' => $company_id,
            'status' => 'A',
            'parent_id' => $feature['parent_id'],
            'categories_path' => '',
        );

        $feature_id = fn_update_product_feature($data, 0, $lang_code);

        if (fn_allowed_for('ULTIMATE')) {
            fn_exim_update_share_feature($feature_id, $company_id);
        }
    } else {
        $feature_id = $data['feature_id'];
    }

    $feature['feature_id'] = $feature_id;
    $feature['company_id'] = $data['company_id'];

    if ($runtime_company_id == 0 || $feature['company_id'] == $company_id) {
        foreach ($feature['names'] as $name_lang_code => $name) {
            if ($name_lang_code != $lang_code) {
                db_query(
                    "UPDATE ?:product_features_descriptions SET ?u WHERE feature_id = ?i AND lang_code = ?s",
                    array('description' => $name),
                    $feature_id,
                    $name_lang_code
                );
            }
        }
    }

    return $feature;
}

/**
 * Find product feature by params with vendor
 *  based on the fn_exim_find_feature
 *
 * @param string    $name       Product feature name
 * @param string    $type       Product feature type
 * @param int       $group_id   Product feature group identification
 * @param string    $lang_code  Language code
 * @param int|null  $company_id Company identifier
 *
 * @return array
 */
function fn_exim_smart_distribution_find_feature($name, $type, $group_id, $lang_code, $company_id = null)
{
    $current_company_id = Registry::get('runtime.company_id');
    $is_simple_ultimate = Registry::get('runtime.simple_ultimate');

    if (!$is_simple_ultimate && $company_id !== null) {
        Registry::set('runtime.company_id', $company_id);
    }

    $condition = db_quote("WHERE description = ?s AND lang_code = ?s AND feature_type = ?s", $name, $lang_code, $type);
    $condition .= db_quote(" AND parent_id = ?i", $group_id);

    if (fn_allowed_for('ULTIMATE')) {
        $condition .= fn_get_company_condition('?:product_features.company_id');
    } elseif (fn_allowed_for('MULTIVENDOR') && $company_id !== null) {
        $condition .= db_quote(" AND (pf.company_id = 0 OR pf.company_id = ?i)", $company_id);
    }
    // [changed part]

    $result = db_get_row(
        'SELECT pf.feature_id, pf.feature_code, pf.feature_type, pf.categories_path, pf.parent_id, pf.status, pf.company_id' .
            ' FROM ?:product_features as pf ' .
            ' LEFT JOIN ?:product_features_descriptions ON pf.feature_id = ?:product_features_descriptions.feature_id ' . $condition
    );

    if (!$is_simple_ultimate && $company_id !== null) {
        Registry::set('runtime.company_id', $current_company_id);
    }

    return $result;
}
function fn_fill_vendor_ugroups_if_empty(&$primary_object_id, &$object, &$pattern, &$options, &$processed_data, &$processing_groups, &$skip_record) {

    if (empty($primary_object_id) && !isset($object['usergroup_ids']) && empty($object['Add user group IDs'])) {
        $company_id = (Registry::get('runtime.company_id')) ? Registry::get('runtime.company_id') : fn_get_company_id_by_name($object['company']);
        if ($company_id) {
            $rcid = Registry::get('runtime.company_id');
            Registry::set('runtime.company_id', $company_id);
            $ugroups = fn_get_usergroups(array('type' => 'C', 'status' => array('A', 'H')));
            Registry::set('runtime.company_id', $rcid);
            $ugroups = array_keys($ugroups);
            if ($ugroups) {
                $object['usergroup_ids'] = implode(',', $ugroups);
            }
        }
    }
}
