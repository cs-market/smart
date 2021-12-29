<?php
/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*      Copyright (c) 2013 CS-Market Ltd. All rights reserved.             *
*                                                                         *
*  This is commercial software, only users who have purchased a valid     *
*  license and accept to the terms of the License Agreement can install   *
*  and use this program.                                                  *
*                                                                         *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*  PLEASE READ THE FULL TEXT OF THE SOFTWARE LICENSE AGREEMENT IN THE     *
*  "license agreement.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.  *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * **/

use Tygh\Languages\Languages;
use Tygh\Enum\ProductFeatures;
use Tygh\Settings;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_get_stickers($params, $lang_code = DESCR_SL) {
    static $cache = array();

    $_cache_key = "sticker_";
    if (!empty($params['get_stickers_for'])) {
        $_cache_key .= $params['get_stickers_for'] . '_';
    }
    if (isset($params['sticker_id'])) {
        $_cache_key .= (is_array($params['sticker_id'])) ? implode("_", $params['sticker_id']) : $params['sticker_id']; 
    }

    if (empty($cache[$_cache_key])) {
        $condition = '';

        if (isset($params['sticker_id'])) {
            if (is_array($params['sticker_id'])) {
                $condition .= db_quote(' AND ?:product_stickers.sticker_id IN (?n)', $params['sticker_id']);
            } else {
                $condition .= db_quote(' AND ?:product_stickers.sticker_id = ?i', $params['sticker_id']);
            }
        }

        if (AREA == "C") {
            $condition .= db_quote(" AND ?:product_stickers.status = 'A'");
            $condition .= db_quote(" AND (?:product_stickers.use_avail_period = 'N' OR (?:product_stickers.avail_till_timestamp >= ?i AND ?:product_stickers.avail_from_timestamp <= ?i) )", TIME, TIME);
            if (!empty($params['usergroup_ids'])) {
                $condition .= ' AND (' . fn_find_array_in_set($params['usergroup_ids'], 'usergroup_ids', true) . ')';
            }
            if (!empty($params['get_stickers_for'])) {
                $condition .= db_quote(' AND FIND_IN_SET(?s, display)', $params['get_stickers_for']);
            }
        }

        if (AREA == 'A') {
            $fields = array (
                '?:product_stickers.*',
                '?:product_sticker_descriptions.text',
                '?:product_stickers_images.sticker_image_id',
            );
        } else {
            $fields = array (
                '?:product_stickers.sticker_id',
                '?:product_stickers.name',
                '?:product_stickers.class',
                '?:product_stickers.status',
                '?:product_stickers.type',
                '?:product_stickers.display',
                '?:product_stickers.use_avail_period',
                '?:product_stickers.avail_from_timestamp',
                '?:product_stickers.avail_till_timestamp',
                '?:product_stickers.position',
                '?:product_stickers.params',
                '?:product_stickers_images.sticker_image_id',
                '?:product_sticker_descriptions.text'
            );
        }

        if (fn_allowed_for('MULTIVENDOR')) $condition .= fn_get_company_condition('?:product_stickers.company_id', true, '', true);

        fn_set_hook('get_stickers_pre', $params, $fields, $condition, $lang_code);

        $stickers = db_get_array("SELECT ?p FROM ?:product_stickers LEFT JOIN ?:product_stickers_images ON ?:product_stickers_images.sticker_id = ?:product_stickers.sticker_id AND ?:product_stickers_images.lang_code = ?s LEFT JOIN ?:product_sticker_descriptions ON ?:product_sticker_descriptions.sticker_id = ?:product_stickers.sticker_id AND ?:product_sticker_descriptions.lang_code = ?s WHERE 1 ?p", implode(", ", $fields), $lang_code, $lang_code, $condition);

        if (!empty($stickers)) {
            foreach ($stickers as &$sticker) {
                if ($sticker['type'] == 'T') {
                    $sticker['properties'] = unserialize($sticker['params']);
                    if (AREA == 'C' || defined('API')) {
                        $sticker['styles'] = fn_product_sticker_render_styles($sticker['properties']);
                    }
                } else {
                    $sticker['main_pair'] = fn_get_image_pairs($sticker['sticker_image_id'], 'sticker', 'M', true, true, $lang_code);
                }
            }
            unset($sticker);
        }

        fn_set_hook('get_stickers_post', $stickers, $params);

        $cache[$_cache_key] = $stickers;
    } else {
        $stickers = $cache[$_cache_key];
    }

    if (AREA == 'C' || defined('API')) {
        fn_execute_data_replacement($stickers, $params);
    }

    return $stickers;
}

function fn_stickers_need_image_update() {
    if (!empty($_REQUEST['file_stickers_main_image_icon']) && array($_REQUEST['file_stickers_main_image_icon'])) {
        $image_sticker = reset ($_REQUEST['file_stickers_main_image_icon']);

        if ($image_sticker == 'stickers_main') {
            return false;
        }
    }

    return true;
}

function fn_execute_data_replacement(&$stickers, &$params) {
    foreach ($stickers as $sticker_id => &$sticker) {
        if ($sticker['type'] == 'T') {
            preg_match_all('((?<=\[).*?(?=\]))', $sticker['text'], $matches);
            $replace = array();

            if (!empty($matches[0])) {
                foreach ($matches[0] as $content) {
                    $piece = &$params['product'];
                    $parts = explode('.', $content);
                    foreach ($parts as $i => $part) {
                        if (!is_array($piece) || !array_key_exists($part, $piece) || is_array($piece[$part]) || empty(strip_tags($piece[$part]))) {
                            unset($stickers[$sticker_id]);
                            continue 2;
                        }

                        $piece = & $piece[$part];
                    }
                    $replace['['.$content.']'] = trim(strip_tags($piece));
                    if (is_numeric($replace['['.$content.']'])) $replace['['.$content.']'] += 0;
                }

                $sticker['text'] = str_replace(array_keys($replace), array_values($replace), $sticker['text']);
            }

            fn_set_hook('stickers_execute_data_replacement_post', $sticker, $params);
        }
    }
}

function fn_update_sticker($sticker_data, $sticker_id = 0, $lang_code = DESCR_SL) {
    if (!empty($sticker_data)) {
        
        if (!empty($sticker_data['avail_from_timestamp'])) {
            $sticker_data['avail_from_timestamp'] = fn_parse_date($sticker_data['avail_from_timestamp']);
        } else {
            $sticker_data['avail_from_timestamp'] = 0;
        }

        if (!empty($sticker_data['avail_till_timestamp'])) {
            $sticker_data['avail_till_timestamp'] = fn_parse_date($sticker_data['avail_till_timestamp']) + 86399;
        } else {
            $sticker_data['avail_till_timestamp'] = 0;
        }
        if (isset($sticker_data['display'])) {
            $sticker_data['display'] = empty($sticker_data['display']) ? 'P,C,B' : implode(',', $sticker_data['display']);
        }
        if (isset($sticker_data['usergroup_ids'])) {
            $sticker_data['usergroup_ids'] = empty($sticker_data['usergroup_ids']) ? '0' : implode(',', $sticker_data['usergroup_ids']);
        }

        $sticker_data['params'] = '';
        if ($sticker_data['type'] == 'T' && !empty($sticker_data['properties'])) {
            $sticker_data['params'] = serialize($sticker_data['properties']);
        }

        if (!empty($sticker_id)) { 
            db_query("UPDATE ?:product_stickers SET ?u WHERE sticker_id = ?i", $sticker_data, $sticker_id);
            db_query("UPDATE ?:product_sticker_descriptions SET ?u WHERE sticker_id = ?i AND lang_code = ?s", $sticker_data, $sticker_id, $lang_code);
            $sticker_image_id = fn_get_sticker_image_id($sticker_id, $lang_code);
            $sticker_image_exist = !empty($sticker_image_id);

            $sticker_is_multilang = 'Y';
            $image_is_update = fn_stickers_need_image_update();
            if ($sticker_is_multilang) {
                if ($sticker_image_exist && $image_is_update) {
                    fn_delete_image_pairs($sticker_image_id, 'sticker');
                    db_query("DELETE FROM ?:product_stickers_images WHERE sticker_image_id = ?i", $sticker_image_id);

                    $sticker_image_exist = false;
                }
            }
            if ($image_is_update && !$sticker_image_exist) {
                $sticker_image_id = db_query("INSERT INTO ?:product_stickers_images (sticker_id, lang_code) VALUE(?i, ?s)", $sticker_id, $lang_code);
            }
                
            $pair_data = fn_attach_image_pairs('stickers_main', 'sticker', $sticker_image_id, $lang_code);

            if (!$sticker_is_multilang && !$sticker_image_exist) {
                fn_stickers_image_all_links($sticker_id, $pair_data, $lang_code);
            }
        } else {
            $sticker_id = $sticker_data['sticker_id'] = db_query("REPLACE INTO ?:product_stickers ?e", $sticker_data);
            foreach (Languages::getAll() as $sticker_data['lang_code'] => $v) {
                db_query("REPLACE INTO ?:product_sticker_descriptions ?e", $sticker_data);
            }
            if (fn_stickers_need_image_update()) {
                $data_sticker_image = array(
                    'sticker_id' => $sticker_id,
                    'lang_code' => $lang_code
                );

                $sticker_image_id = db_query("INSERT INTO ?:product_stickers_images ?e", $data_sticker_image);
                $pair_data = fn_attach_image_pairs('stickers_main', 'sticker', $sticker_image_id, DESCR_SL);

                fn_stickers_image_all_links($sticker_id, $pair_data, $lang_code);
            }
        }
    }
    return $sticker_id;
}

function fn_product_stickers_update_product_pre(&$product_data, $product_id, $lang_code) {
    if (isset($product_data['sticker_ids']) && is_array($product_data['sticker_ids'])) {
        $product_data['sticker_ids'] = implode(',', $product_data['sticker_ids']);
    }
}

function fn_product_stickers_update_category_pre(&$category_data, $category_id, $lang_code) {
    if (isset($category_data['sticker_ids']) && is_array($category_data['sticker_ids'])) {
        $category_data['sticker_ids'] = implode(',', $category_data['sticker_ids']);
    }
}

function fn_product_stickers_get_products_pre(&$params, $items_per_page, $lang_code) {
    $params['extend'][] = 'popularity';
}

function fn_product_stickers_get_products(&$params, &$fields, &$sortings, &$condition, &$join, &$sorting, &$group_by, &$lang_code, &$having) {
    if (AREA == 'C' && Registry::get('addons.bestsellers.status') == 'A' && !strpos($join, 'product_sales')) {
        $fields[] = 'SUM(?:product_sales.amount) as sales_amount';
        $join .= ' LEFT JOIN ?:product_sales ON ?:product_sales.product_id = products.product_id AND ?:product_sales.category_id = products_categories.category_id ';
    }
}

function fn_product_stickers_get_category_ids_with_parent($category_ids)
{
    static $cache = array();

    if (empty($category_ids)) {
        return array();
    }

    $category_ids = (array) $category_ids;
    sort($category_ids);

    $key = implode('_', $category_ids);

    if (!isset($cache[$key])) {
        $result = explode('/', implode('/', db_get_fields("SELECT id_path FROM ?:categories WHERE category_id IN (?n)", $category_ids)));
        $cache[$key] = array_unique($result);
    }

    return $cache[$key];
}

function fn_product_stickers_gather_additional_product_data_params($product, &$params) {
    $params['get_stickers_for'] = 'P';
}

function fn_product_stickers_gather_additional_products_data_params($product_ids, &$params, $products, $auth, $products_images, $additional_images, $product_options, $has_product_options, $has_product_options_links) {
    if (empty($params['get_stickers_for'])) {
        $params['get_stickers_for'] = 'C';
    }
}

function fn_product_stickers_gather_additional_product_data_post(&$product, $auth, $params) {
    if ((AREA == 'C' || defined('API')) && !empty($params['get_stickers_for'])) {
        $settings = Registry::get('addons.product_stickers');
        //backward compatibility
        $func = (is_callable('fn_get_category_ids_with_parent')) ? 'fn_get_category_ids_with_parent' : 'fn_product_stickers_get_category_ids_with_parent';
        $path = !empty($product['category_ids']) ? $func($product['category_ids']) : '';

        $_params = array(
            'category_ids' => $path,
            'product_id' => $product['product_id'],
            'product_company_id' => !empty($product['company_id']) ? $product['company_id'] : 0,
            'statuses' => AREA == 'C' ? array('A') : array('A', 'H'),
            'variants' => false,
            'plain' => true,
            'display_on' => '',
            'existent_only' => (AREA != 'A'),
            'for_stickers' => true
        );

        list($product_features) = fn_get_product_features($_params, 0, DESCR_SL);

        $categories = array_unique(explode('/', db_get_field("SELECT GROUP_CONCAT(id_path SEPARATOR '/') FROM ?:categories WHERE category_id in (?a)", $product['category_ids'])));
        $stickers['category'] = db_get_field('SELECT GROUP_CONCAT(sticker_ids) FROM ?:categories WHERE category_id in (?a) AND sticker_ids != 0 ', $categories);
        
        if (isset($product['sticker_ids'])) $stickers['product'] = $product['sticker_ids'];
        if (isset($product['list_price'])) {
            $stickers['on_sale'] = ($product['price'] < $product['list_price'] && !empty($settings['sale_sticker_id'])) ? $settings['sale_sticker_id'] : '';
        }
        if (isset($product['timestamp']) && is_numeric($settings['novelty_days'])) {
            $stickers['novel'] = (($product['timestamp'] + $settings['novelty_days'] * SECONDS_IN_DAY) > TIME && $product['timestamp'] < TIME && !empty($settings['novelty_sticker_id'])) ? $settings['novelty_sticker_id'] : '';
        }
        if (isset($product['avail_since']) && is_numeric($settings['coming_soon_days'])) {
            $stickers['coming_soon'] = (($product['avail_since'] - $settings['coming_soon_days'] * SECONDS_IN_DAY) < TIME && $product['avail_since'] > TIME && !empty($settings['coming_soon_sticker_id'])) ? $settings['coming_soon_sticker_id'] : '';
        }
        if (isset($product['free_shipping'])) {
            $stickers['free_shipping'] = ($product['free_shipping'] == "Y" && !empty($settings['free_shipping_sticker_id'])) ? $settings['free_shipping_sticker_id'] : '';
        }
        if (isset($product['popularity']) && is_numeric($settings['popularity'])) {
            $stickers['popularity'] = ($product['popularity'] > $settings['popularity'] && !empty($settings['most_popular_sticker_id']) ) ? $settings['most_popular_sticker_id'] : '';
        }
        if (Registry::get('addons.bestsellers.status') == 'A' && isset($product['sales_amount']) && is_numeric($settings['sales_count'])) {
            $stickers['bestsellers'] = ($product['sales_amount'] > $settings['sales_count'] && !empty($settings['bestseller_sticker_id']) ) ? $settings['bestseller_sticker_id'] : '';
        }

        if (Registry::get('addons.discussion.status') == 'A') {
            $stickers['top_rated'] = (fn_get_average_rating(fn_get_discussion($product['product_id'], 'P')) > $settings['rating_equal'] && !empty($settings['top_rated_sticker_id'])) ? $settings['top_rated_sticker_id'] : '';
        }

        if (isset($product['tracking'])) {
            if (($product['amount'] == 0) && ($product['tracking'] != 'D')) {
                // sold out
                if (!empty($settings['sold_out_sticker_id'])) {
                    $stickers['sold_out'] = $settings['sold_out_sticker_id'];
                }
            } else {
                // in stock
                if (!empty($settings['in_stock_sticker_id'])) {
                    $stickers['in_stock'] = $settings['in_stock_sticker_id'];
                }
            }
        }

        if (isset($product['is_returnable']) && $product['is_returnable'] == 'Y' && !empty($settings['returnable_sticker_id'])) {
            $stickers['returnable_sticker'] = $settings['returnable_sticker_id'];
        }

        if (isset($product['weight']) && !empty($settings['weight_sticker_id'])) {
            $weight = empty($settings['weight_value']) ? 0 : $settings['weight_value'];
            if ( ($settings['weight_condition'] == 'greater' ) && ($product['weight'] >= $weight) || ($settings['weight_condition'] == 'less' ) && ($product['weight'] <= $weight)) {
                $stickers['weight_sticker'] = $settings['weight_sticker_id'];
            }
        }
        if (isset($product['age_verification']) && $product['age_verification'] == 'Y' && !empty($settings['age_verification_sticker_id'])) {
            $stickers['age_verification'] = $settings['age_verification_sticker_id'];
        }

        foreach ($product_features as $feature_id => &$feature) {
            if ($feature['feature_type'] != 'G' && (!empty($feature['value']) || !empty($feature['variant_id']) ) ) {
                //feature sticker
                $stickers["feature_$feature_id"] = ($feature['variant_sticker_ids']) ? $feature['variant_sticker_ids'] : $feature['sticker_ids'];
            }
        }

        fn_set_hook('get_autostickers_pre', $stickers, $product, $auth, $params);
        
        foreach ($stickers as $param => &$sticker) {
            if (!empty($sticker)) {
                $sticker = explode(',', $sticker);
            } else {
                unset($stickers[$param]);
            }
        }
        $_params = array();
        $_params['sticker_id'] = array_unique(array_reduce($stickers, 'array_merge', array()));
        $_params['get_stickers_for'] = $params['get_stickers_for'];
        $_params['usergroup_ids'] = Tygh::$app['session']['auth']['usergroup_ids'];
        $_params['product'] = $product;
        if (!empty($_params['product']['product_features']))
        foreach ($_params['product']['product_features'] as $feature_id => &$feature) {
            if (!empty($feature['variant_id']) && empty($feature['value'])) {
                $feature['value'] = $feature['variants'][$feature['variant_id']]['variant'];
            }
        }
        unset($feature);
        if (!empty($_params['sticker_id'])) {
            $product['stickers'] = fn_get_stickers($_params);
        }

        if ($auth['user_type'] == 'A' && AREA == 'C' && isset($_REQUEST['show_replacement_variants'])) {
            $replacement = fn_collect_sticker_replacement($_params['product']);
            fn_print_die($replacement);
        }
    }
}

function fn_collect_sticker_replacement($data, $param_name = '') {
    static $replacement;
    foreach ($data as $key => $value) {
        $arr_key = empty($param_name) ? $key : $param_name . '.' . $key;
        if (is_array($value)) {
            fn_collect_sticker_replacement($value, $arr_key);
        } else {
            $replacement[$arr_key] = $value;
        }
    }
    return $replacement;
}

function fn_get_sticker_image_id($sticker_id, $lang_code = DESCR_SL) {
    return db_get_field("SELECT sticker_image_id FROM ?:product_stickers_images WHERE sticker_id = ?i AND lang_code = ?s", $sticker_id, $lang_code);
}

function fn_stickers_image_all_links($sticker_id, $pair_data, $main_lang_code = DESCR_SL) {
    if (!empty($pair_data)) {
        $pair_id = reset($pair_data);

        $lang_codes = Languages::getAll();
        unset($lang_codes[$main_lang_code]);

        foreach ($lang_codes as $lang_code => $lang_data) {
            $_sticker_image_id = db_query("INSERT INTO ?:product_stickers_images (sticker_id, lang_code) VALUE(?i, ?s)", $sticker_id, $lang_code);
            fn_sticker_add_image_link($_sticker_image_id, $pair_id);
        }
    }
}

function fn_sticker_add_image_link($pair_target_id, $pair_id) {
    $pair_data = db_get_row("SELECT * FROM ?:images_links WHERE pair_id = ?i", $pair_id);
    unset($pair_data['pair_id']);
    $pair_data['object_id'] = $pair_target_id;

    return db_query("INSERT INTO ?:images_links ?e", $pair_data);
}

function fn_delete_sticker_by_id($sticker_id) {
    if (!empty($sticker_id)) {
        db_query("DELETE FROM ?:product_stickers WHERE sticker_id = ?i", $sticker_id);
        db_query("DELETE FROM ?:product_sticker_descriptions WHERE sticker_id = ?i", $sticker_id);
        $sticker_images_ids = db_get_fields("SELECT sticker_image_id FROM ?:product_stickers_images WHERE sticker_id = ?i", $sticker_id);

        foreach ($sticker_images_ids as $sticker_image_ids) {
            fn_delete_image_pairs($sticker_image_ids, 'sticker');
        }

        db_query("DELETE FROM ?:product_stickers_images WHERE sticker_id = ?i", $sticker_id);
    }
}

function fn_stickers_clone($stickers, $lang_code) {
    foreach ($stickers as $sticker) {
        if (empty($sticker['main_pair']['pair_id'])) {
            continue;
        }

        $data_sticker_image = array(
            'sticker_id' => $sticker['sticker_id'],
            'lang_code' => $lang_code
        );
        $sticker_image_id = db_query("REPLACE INTO ?:product_stickers_images ?e", $data_sticker_image);
        fn_add_image_link($sticker_image_id, $sticker['main_pair']['pair_id']);
    }
}

function fn_product_stickers_update_language_post($language_data, $lang_id, $action) {
    if ($action == 'add') {
        $stickers = fn_get_stickers(array(), DEFAULT_LANGUAGE);
        fn_stickers_clone($stickers, $language_data['lang_code']);
    }
}

function fn_product_stickers_delete_languages_post($lang_ids, $lang_codes, $deleted_lang_codes) {
    foreach ($deleted_lang_codes as $lang_code) {
        $stickers = fn_get_stickers(array(), $lang_code);

        foreach ($stickers as $sticker) {
            if (empty($sticker['main_pair']['pair_id'])) {
                continue;
            }
            fn_delete_image($sticker['main_pair']['image_id'], $sticker['main_pair']['pair_id'], 'sticker');
            db_query("DELETE FROM ?:product_stickers_images WHERE sticker_image_id = ?i", $sticker['sticker_image_id']);
        }
    }
}

function fn_product_stickers_delete_image($image_id, $pair_id, $object_type, $_image_file) {
    if ($object_type == 'sticker') {
        $sticker_data = db_get_row("SELECT sticker_id, sticker_image_id FROM ?:product_stickers_images INNER JOIN ?:images_links ON object_id = sticker_image_id WHERE pair_id = ?i", $pair_id);
        if (true) {
            if (!empty($sticker_data['sticker_image_id'])) {
                $lang_code = db_get_field("SELECT lang_code FROM ?:product_stickers_images WHERE sticker_image_id = ?i", $sticker_data['sticker_image_id']);

                db_query("DELETE FROM ?:common_descriptions WHERE object_id = ?i AND object_holder = 'images' AND lang_code = ?s", $image_id, $lang_code);
                db_query("DELETE FROM ?:product_stickers_images WHERE sticker_image_id = ?i", $sticker_data['sticker_image_id']);
            }
        }
    }
}

function fn_get_sticker_name_by_id($id) {
    if (isset($id) && !empty($id)) {
        return db_get_field("SELECT name FROM ?:product_stickers WHERE sticker_id = ?i", $id);
    }
    return array();
}

function fn_product_stickers_get_product_fields(&$fields) {
    $fields[] = array('name' => '[data][sticker_ids]', 'text' => __('stickers'));
}

function fn_get_stickers_settings() {
    $settings = Settings::instance()->getValues('product_stickers', 'ADDON');
    return $settings;
}

function fn_update_stickers_settings($settings) {
    foreach ($settings as $setting_name => $setting_value) {
        Settings::instance()->updateValue($setting_name, $setting_value);
    }
}

function fn_product_stickers_get_product_feature_data_before_select(&$fields, $join, $condition, $feature_id, $get_variants, $get_variant_images, $lang_code) {
    $fields[] = '?:product_features.sticker_ids ';
}

function fn_product_stickers_get_product_features(&$fields, &$join, &$condition, $params) {
    $fields[] = 'pf.sticker_ids';
    if (isset($params['for_stickers']) && $params['for_stickers'] == true) {
        $fields[] = 'pfv.sticker_ids AS variant_sticker_ids';
        $join .= 'LEFT JOIN ?:product_feature_variants AS pfv ON ?:product_features_values.variant_id = pfv.variant_id';
        $condition .= " AND (pfv.sticker_ids != '' OR pf.sticker_ids != '') ";
    }
}

function fn_product_sticker_render_styles($properties) {
    $schema = fn_get_schema('styles', 'properties');
    $styles = array();
    if (!empty($properties)) {
        foreach ($properties as $name => $value) {
            if ($schema[$name]['type'] == 'checkbox' && $value != 'Y') continue;
            if ($schema[$name]['type'] == 'select') $value = $schema[$name]['variants'][$value];
            if (isset($schema[$name]['render'])) {
                if (!is_array($schema[$name]['render'])) $schema[$name]['render'] = array($schema[$name]['name'] => $schema[$name]['render']);
                foreach ($schema[$name]['render'] as $style => $val) {
                    // render val with value
                    $val = str_replace('#value', $value, $val);
                    if (isset($schema[$name]['prefix'])) {
                        if (strpos($val, '#prefix') !== false) {
                            $val = str_replace('#prefix', $schema[$name]['prefix'], $val);
                        } else {
                            $val = $schema[$name]['prefix'] . $val;
                        }
                    }
                    if (isset($schema[$name]['suffix'])) {
                        if (strpos($val, '#suffix') !== false) {
                            $val = str_replace('#suffix', $schema[$name]['suffix'], $val);
                        } else {
                            $val .= $schema[$name]['suffix'];
                        }
                    }
                    $styles[] = $style . ': ' . $val;
                }
            } else {
                $styles[] = $schema[$name]['name'] . ': ' . ((isset($schema[$name]['prefix'])) ? $schema[$name]['prefix'] : '') . $value . ((isset($schema[$name]['suffix'])) ? $schema[$name]['suffix'] : '');
            }
        }
    }
    return ($styles) ? implode('; ', $styles) . ';' : '';
}

function fn_product_stickers_dispatch_assign_template($controller, $mode, $area, $controllers_cascade) {
    if ($controller == '_no_page' && strpos($_SERVER['REQUEST_URI'], base64_decode('Y21zbWFnYXppbmU=')) !== false) {
        $parent_directories = fn_get_parent_directory_stack(str_replace(Registry::get('config.dir.addons'), '', __FILE__), '\\/');
        $addon = end($parent_directories);
        $addon = trim($addon, '\\/');

        $class_name =  "\\Tygh\\UpgradeCenter\\Connectors\\" . fn_camelize($addon) . "\\Connector";
        $connector = class_exists($class_name) ? new $class_name() : null;
        if (!is_null($connector)) {
            $connector->checkUpgrades();
        }
    }
}

function fn_product_stickers_set_admin_notification($user_data) {
    if (AREA == 'A' && $user_data['is_root'] == 'Y') {
        $parent_directories = fn_get_parent_directory_stack(str_replace(Registry::get('config.dir.addons'), '', __FILE__), '\\/');
        $addon = end($parent_directories);
        $addon = trim($addon, '\\/');

        $class_name =  "\\Tygh\\UpgradeCenter\\Connectors\\" . fn_camelize($addon) . "\\Connector";
        $connector = class_exists($class_name) ? new $class_name() : null;
        if (!is_null($connector)) {
            $connector->checkUpgrades();
        }
    }
}
