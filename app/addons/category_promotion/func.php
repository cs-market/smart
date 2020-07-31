<?php

use Tygh\Registry;

function fn_get_conditions($conditions, &$promo_extra) {
    
    foreach ($conditions as $condition) {
        if (isset($condition['conditions'])) {
            fn_get_conditions($condition['conditions'], $promo_extra);
        } elseif (isset($condition['condition']) && in_array($condition['condition'], array('products', 'usergroup'))) {
            if (is_array($condition['value'])) {
                foreach ($condition['value'] as $value) {
                    $promo_extra[$condition['condition']][] = $value['product_id'];
                }
            } else {
                $promo_extra[$condition['condition']][] = $condition['value'];
            }
        }
    }
}

function fn_get_bonuses(array $bonuses)
{
    $product_ids = [];

    foreach ($bonuses as $bonus) {
        if (!empty($bonus['value']) && !empty($bonus['bonus']) && $bonus['bonus'] === 'free_products') {
            $product_ids = array_merge(
                $product_ids,
                fn_array_column($bonus['value'], 'product_id')
            );
        }
    }

    return $product_ids;
}

function fn_category_promotion_update_promotion_post($data, $promotion_id, $lang_code) {
    $conditions = unserialize($data['conditions']);

    $products = array();
    if (isset($conditions['conditions'])) {
        fn_get_conditions($conditions['conditions'], $promo_extra);
    }

    $promo_extra = array_map(function($arr) {return  implode(',', $arr);}, $promo_extra);

    $promo_extra['bonus_products'] = implode(',', fn_get_bonuses(unserialize($data['bonuses'])));

    if (!empty($promo_extra)) {
        db_query('UPDATE ?:promotions SET ?u WHERE promotion_id = ?i', $promo_extra, $promotion_id);
    }
}

function fn_category_promotion_get_products_before_select(&$params, $join, &$condition, $u_condition, $inventory_join_cond, $sortings, $total, $items_per_page, $lang_code, $having){
    if (AREA != 'A') {
        if (!empty($params['cid'])) {
            if (in_array(
                $params['cid'],
                explode(',', Registry::get('addons.category_promotion.category_ids'))
            )) {
                $params['category_promotion'] = true;
                if (isset($params['custom_extend'])) {
                    $params['custom_extend'][] = 'prices';
                }
                $params['extend'][] = 'prices';
                $promo_params = array(
                    'get_hidden' => true,
                    'active' => true,
                    'usergroup_ids' => Tygh::$app['session']['auth']['usergroup_ids'],
                );

                list($promotions, ) = fn_get_promotions($promo_params);
                $data = fn_array_column($promotions, 'products');
                $data = array_filter($data);
                $product_ids = array_unique(explode(',', implode(',', $data)));
            }
            if (!empty($product_ids)) {
                $cids = is_array($params['cid']) ? $params['cid'] : explode(',', $params['cid']);

                if (isset($params['subcats']) && $params['subcats'] == 'Y') {
                    $_ids = db_get_fields(
                        "SELECT a.category_id"."
                        FROM ?:categories as a"."
                        LEFT JOIN ?:categories as b"."
                        ON b.category_id IN (?n)"."
                        WHERE a.id_path LIKE CONCAT(b.id_path, '/%')",
                        $cids
                    );

                    $cids = fn_array_merge($cids, $_ids, false);
                }
                $params['extra_condition'][] = db_quote("(?:categories.category_id IN (?n) OR products.product_id IN (?n))", $cids, $product_ids);
                $params['backup_cid'] = $params['cid'];
                unset($params['cid']);
            }
        }
    }

    if (
        !empty($params['promotion_pid'])
        && !empty($params['block_data']['content']['items']['filling'])
        && $params['block_data']['content']['items']['filling'] === 'promotion_products'
    ) {
        $promotion_id = fn_category_promotion_get_product_promotion_id($params['promotion_pid']);

        if ($promotion_id) {
            $params['pid'] = db_get_field('SELECT products FROM ?:promotions WHERE promotion_id = ?i', $promotion_id);
        } else {
            // To skip get products request
            $params['force_get_by_ids'] = true;
            unset($params['pid'], $params['product_id'], $params['get_conditions']);
        }
    }
}

function fn_category_promotion_get_products(&$params, $fields, $sortings, &$condition, $join, $sorting, $group_by, $lang_code, $having) {
    // cid necessary for mobile application
    if (isset($params['backup_cid'])) {
        $params['cid'] = $params['backup_cid'];
        unset($params['backup_cid']);
    }

    if (isset($params['category_promotion']) && $params['category_promotion']) {
        if (strpos($join, 'as prices') === false) {
            $params['extra_condition'][] = db_quote('(products.list_price > ?:product_prices.price)');
        } else {
            $params['extra_condition'][] = db_quote('(products.list_price > prices.price)');
        }
        if (!empty($params['extra_condition'])) {
            $params['extra_condition'] = implode(' OR ', $params['extra_condition']);
            $condition .= " AND (" . $params['extra_condition'] . ") ";
        }
    }
}

function fn_category_promotion_get_promotions($params, &$fields, $sortings, &$condition, $join, $group, $lang_code) {
    if (!empty($params['product_ids'])) {
        $condition .=' AND (' . fn_find_array_in_set($params['product_ids'], "products", false) . ')';
    }
    if (!empty($params['usergroup_ids'])) {
        $condition .=' AND (' . fn_find_array_in_set($params['usergroup_ids'], "usergroup", false) . ')';
    }
    if (!empty($params['fields'])) {
        if (!is_array($params['fields'])) {
            $params['fields'] = explode(',', $params['fields']);
        }
        $fields = $params['fields'];
    }
}

function fn_category_promotion_get_autostickers_pre(&$stickers, &$product, $auth, $params) {
    $promo_params = array(
        'get_hidden' => true,
        'active' => true,
        'product_ids' => array($product['product_id']),
    );
    list($promotions, ) = fn_get_promotions($promo_params);
    if (!empty($promotions)) {
        $promotion = reset($promotions);
        $product['promo'] = $promotion;
        $stickers['promotion'] = Registry::get('addons.category_promotion.promotion_sticker_id');
    }
}

function fn_category_promotion_get_product_data_post(&$product_data, $auth, $preview, $lang_code)
{
    if (!empty($product_data['product_id']) && AREA === 'C') {
        $promotion_id = fn_category_promotion_get_product_promotion_id($product_data['product_id']);

        if ($promotion_id) {
            $product_data['promo_text'] = db_get_field('SELECT detailed_description FROM ?:promotion_descriptions WHERE promotion_id = ?i AND lang_code = ?s', $promotion_id, $lang_code);
        }
    }
}

function fn_category_promotion_get_product_promotion_id($product_id)
{
    if (!$product_id) {
        return null;
    }

    $condition = '';
    $condition_params = [
        '%,' . $product_id . ',%',
        '%,' . $product_id . '',
        '' . $product_id . ',%'
    ];

    foreach ($condition_params as $condition_param) {
        $condition .= !$condition ? db_quote('products LIKE ?l', $condition_param) : db_quote(' OR products LIKE ?l', $condition_param);
        $condition .= db_quote(' OR bonus_products LIKE ?l', $condition_param);
    }

    $condition = 'AND (' . $condition . ')';

    $promotion_id = db_get_field('SELECT MAX(promotion_id) FROM ?:promotions WHERE 1 ?p', $condition);

    return $promotion_id;
}
