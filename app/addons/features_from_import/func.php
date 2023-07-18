<?php

defined('BOOTSTRAP') or die('Access denied');

function fn_advanced_import_set_product_features_pre($product_id, $features_list, $variants_delimiter = '///', $preset)
{
    if (!$features_list || !is_array($features_list)) {
        return;
    }

    static $created_features;

    $features_mapper = Tygh::$app['addons.advanced_import.features_mapper'];
    $main_lang = $features_mapper->getMainLanguageCode($features_list);
    foreach ($features_list[$main_lang] as $feature_id => $value) {
        if (!is_numeric($feature_id)) {
            if (strpos($feature_id, 'create-new-feature:') === 0) {
                unset($features_list[$main_lang][$feature_id]);
                $feature_name = str_replace('create-new-feature:', '', $feature_id);
                $company_id = isset($preset['company_id']) ? (int) $preset['company_id'] : (int) fn_get_runtime_company_id();

                if (array_key_exists($feature_name, $created_features)) {
                    $feature_id = $created_features[$feature_name];
                } else {
                    $data = array(
                        'feature_id' => 0,
                        'description' => $feature_name,
                        'feature_type' => 'S',
                        'lang_code' => $main_lang,
                        'company_id' => $company_id,
                        'status' => 'A',
                        'parent_id' => 0,
                        'categories_path' => '',
                    );

                    $created_features[$feature_name] = $feature_id = fn_update_product_feature($data, 0, $main_lang);
                }

                $features_list[$main_lang][$feature_id] = $value;
            }
        }
    }

    return fn_advanced_import_set_product_features($product_id, $features_list, $variants_delimiter);
}
