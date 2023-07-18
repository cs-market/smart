<?php

use Tygh\Registry;
use Tygh\Enum\SiteArea;

defined('BOOTSTRAP') or die('Access denied');

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

function fn_get_promotion_bonus_products(array $bonuses)
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

function fn_get_promotion_condition_categories(array $conditions, array &$caregory_ids = [])
{
    if (!empty($conditions['conditions'])) {
        foreach ($conditions['conditions'] as $condition) {
            if (!empty($condition['conditions'])) {
                fn_get_promotion_condition_categories($condition, $caregory_ids);
            } elseif (
                !empty($condition['value'])
                && !empty($condition['operator'])
                && $condition['operator'] === 'in'
                && !empty($condition['condition'])
                && $condition['condition'] === 'categories'
            ) {
                $caregory_ids[] = $condition['value'];
            }
        }
    }

    return array_unique($caregory_ids);
}

function fn_category_promotion_update_promotion_post($data, $promotion_id, $lang_code) {
    $conditions = unserialize($data['conditions']);

    $products = array();
    if (isset($conditions['conditions'])) {
        fn_get_conditions($conditions['conditions'], $promo_extra);
    }

    $default_promo_extra = ['products' => '', 'usergroup' => ''];
    $promo_extra = array_map(function($arr) {return  implode(',', $arr);}, $promo_extra);
    $promo_extra = fn_array_merge($default_promo_extra, $promo_extra);

    $promo_extra['bonus_products'] = implode(',', fn_get_promotion_bonus_products(unserialize($data['bonuses'])));
    $promo_extra['condition_categories'] = implode(',', fn_get_promotion_condition_categories(unserialize($data['conditions'])));

    if (!empty($promo_extra)) {
        db_query('UPDATE ?:promotions SET ?u WHERE promotion_id = ?i', $promo_extra, $promotion_id);
    }
}

function fn_category_promotion_get_products_before_select(&$params, $join, &$condition, $u_condition, $inventory_join_cond, $sortings, $total, $items_per_page, $lang_code, $having){
    if (SiteArea::isStorefront(AREA)) {
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
                    'category_id' => $params['cid'],
                );

                list($promotions, ) = fn_get_promotions($promo_params);

                $data = fn_array_column($promotions, 'products');
                $data = array_filter($data);
                $product_ids = array_unique(explode(',', implode(',', $data)));

                fn_set_hook('category_promotion_get_products_before_select', $params);
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
                $params['extend'][] = 'categories';
                $params['extend'][] = 'product_name';
            }
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
        unset($params['extra_condition']);
    }
}

function fn_category_promotion_get_promotions_pre(&$params, $items_per_page, $lang_code) {
    if (defined('ORDER_MANAGEMENT') && !empty($params['promotion_id'])) {
        return;
    }
    if (SiteArea::isStorefront(AREA)) {
        unset($params['get_hidden']);
    }
    $params += $_REQUEST;
}

function fn_category_promotion_get_promotions($params, &$fields, $sortings, &$condition, $join, $group, $lang_code) {
    if (!empty($params['product_ids'])) {
        $condition .=' AND (' . fn_find_array_in_set($params['product_ids'], "products", false) . ')';
    }
    if (!empty($params['category_id'])) {
        $condition .=' AND (' . fn_find_array_in_set([$params['category_id']], "categories", true) . ')';
    }
}

function fn_category_promotion_get_promotions_post($params, $items_per_page, $lang_code, &$promotions) {
    if (defined('ORDER_MANAGEMENT')) {
        return;
    }
    if (SiteArea::isStorefront(AREA)) {
        $promotions = array_filter($promotions, function($promotion) {
            $conditions = (is_string($promotion['conditions'])) ? unserialize($promotion['conditions']) : $promotion['conditions'];
            fn_cleanup_promotion_condition($conditions, ['usergroup', 'users']);
            $promotion['conditions'] = $conditions;
            $data = $cart_products = [];
            return fn_check_promotion_conditions($promotion, $data, Tygh::$app['session']['auth'], $cart_products);
        });
    }
}

function fn_cleanup_promotion_condition(&$conditions_group, $allowed_conditions) {
    if (!empty($conditions_group['conditions'])) {
        foreach ($conditions_group['conditions'] as $i => &$group_item) {
            if (isset($group_item['conditions'])) {
                fn_cleanup_promotion_condition($group_item, $allowed_conditions);
                if (empty($group_item['conditions'])) unset($conditions_group['conditions'][$i]);
            } elseif ((is_array($allowed_conditions) && !in_array($group_item['condition'], $allowed_conditions)) || $group_item['condition'] == $allowed_conditions) {
                unset($conditions_group['conditions'][$i]);
            }
        }
    }
    //if (empty($conditions_group['conditions'])) unset($conditions_group['conditions']);
}

function fn_category_promotion_get_autostickers_pre(&$stickers, &$product, $auth, $params) {
    if (!empty($params['get_for_one_product'])) {
        $promo_params = array(
            'get_hidden' => true,
            'active' => true,
            'product_ids' => array($product['product_id']),
        );

        list($promotions, ) = fn_get_promotions($promo_params);
        if (!empty($promotions)) {
            $promotion = reset($promotions);
            $product['promo'] = $promotion;
            $stickers['promotion'] = (!empty($promotion['sticker_ids'])) ? $promotion['sticker_ids'] : Registry::get('addons.category_promotion.promotion_sticker_id');
        }
    }
}

function fn_category_promotion_gather_additional_products_data_post($product_ids, $params, &$products, $auth, $lang_code) {
    if (Registry::get('addons.product_stickers.status') == 'A') {
        if (empty($params['get_for_one_product'])) {
            $promo_params = array(
                'get_hidden' => true,
                'active' => true,
                'product_ids' => $product_ids,
            );
            list($promotions, ) = fn_get_promotions($promo_params);
            if (!empty($promotions)) {
                foreach($promotions as $promotion) {
                    if (!empty($promotion['products'])) {
                        foreach(explode(',', $promotion['products']) as $product_id) {
                            if (!in_array($product_id, $product_ids)) continue;
                            foreach ($products as &$product) {
                                if ($product['product_id'] != $product_id) continue;
                                
                                if (!empty($product['promo'])) continue;

                                $product['promo'] = $promotion;
                                $sticker_id = (!empty($promotion['sticker_ids'])) ? $promotion['sticker_ids'] : Registry::get('addons.category_promotion.promotion_sticker_id');

                                $_params = array();
                                $_params['sticker_id'] = (!empty($promotion['sticker_ids'])) ? $promotion['sticker_ids'] : Registry::get('addons.category_promotion.promotion_sticker_id');
                                $_params['get_stickers_for'] = $params['get_stickers_for'];
                                $_params['usergroup_ids'] = Tygh::$app['session']['auth']['usergroup_ids'];
                                $_params['product'] = $product;
                                if ($res = fn_get_stickers($_params)) {
                                    if (empty($product['stickers'])) {
                                        $product['stickers'] = $res;
                                    } else {
                                        $product['stickers'] = array_merge($products[$product_id]['stickers'], $res);
                                    }
                                }
                            }
                            unset($product);
                        }
                    }
                }
            }
        }
    }
}

function fn_category_promotion_split_promotion_by_type($promotions) {
    $simple_promotions = array_filter($promotions, function($v) {
        return $v['view_separate'] == 'N';
    });
    $promotions = array_filter($promotions, function($v) {
        return $v['view_separate'] == 'Y';
    });
    $data = fn_array_column($simple_promotions, 'products');
    $data = array_filter($data);
    $product_ids = array_unique(explode(',', implode(',', $data)));
    if ($product_ids) {
        $params = $_REQUEST;
        // TODO pass request into params
        unset($params['items_per_page'], $params['page']);
        $params['extend'] = ['categories', 'description'];
        $params['pid'] = $product_ids;
        list($products, $search) = fn_get_products($params);
    }
    return array($promotions, $products, $search);
}

function fn_category_promotion_get_cart_promotioned_products($id, $products) {
    $product_ids = [];
    $promotion = fn_get_promotion_data($id);
    if ($promotion['condition_categories']) {
        $category_ids = explode(',', $promotion['condition_categories']);
        list($categories_products) = fn_get_products(['cid' => $category_ids, 'subcats' => 'Y', 'load_products_extra_data' => false]);
        if ($categories_products) {
            $product_ids = array_keys($categories_products);
        }
        return array_filter($products, function($v) use ($product_ids) {return in_array($v['product_id'], $product_ids);});
    } elseif ($promotion['products']) {
        if ($product_condition = fn_find_promotion_condition($promotion['conditions'], 'products')) {
            $conditioned_products = [];
            foreach ($product_condition['value'] as $data) {
                $conditioned_products[$data['product_id']] = $data['amount'];
            }
            return array_filter($products, function($v) use ($conditioned_products) {
                return (isset($conditioned_products[$v['product_id']]) && $v['amount'] >= $conditioned_products[$v['product_id']]);
            });
        }
    }
    
    return [];
}

function fn_category_promotion_check_total_conditioned_products($id, $promo, $products) {
    $cart_products = fn_category_promotion_get_cart_promotioned_products($id, $products);

    if ($cart_products) {
        $subtotal = array_sum(array_column($cart_products, 'subtotal'));
        return fn_promotion_validate_attribute($subtotal, $promo['value'], $promo['operator']);
    }

    return false;
}

function fn_category_promotion_check_amount_conditioned_products($id, $promo, $products) {
    $cart_products = fn_category_promotion_get_cart_promotioned_products($id, $products);

    return array_sum(array_column($cart_products, 'amount'));
}

function fn_category_promotion_check_unique_amount_conditioned_products($id, $promo, $products) {
    $cart_products = fn_category_promotion_get_cart_promotioned_products($id, $products);

    return count(array_unique(array_column($cart_products, 'product_id')));
}

function fn_category_promotion_pre_add_to_cart(&$product_data, $cart, $auth, $update) {
    if ($update) {
        foreach ($product_data as $key => &$data) {
            if (isset($cart['products'][$key]['extra']['limit_discount_bonus_by_amount_packages'])) {
                $data['extra']['limit_discount_bonus_by_amount_packages'] = $cart['products'][$key]['extra']['limit_discount_bonus_by_amount_packages'];
            }
        }
        unset($data);
    }
}

function fn_category_promotion_apply_cart_rule($bonus, &$cart, &$auth, &$cart_products) {
    if (in_array($bonus['bonus'], ['discount_on_products_from_conditions', 'static_discount_on_products_from_conditions'])) {
        static $promotions = [];

        $condition_products = fn_category_promotion_get_cart_promotioned_products($bonus['promotion_id'], $cart_products);

        if (!isset($promotions[$bonus['promotion_id']])) {
            $promotions[$bonus['promotion_id']] = fn_get_promotion_data($bonus['promotion_id']);
        }

        $promotion = $promotions[$bonus['promotion_id']];
        if (!isset($cart['limit_discount_bonus_by_amount_packages'][$bonus['promotion_id']])) {
            $cart['limit_discount_bonus_by_amount_packages'][$bonus['promotion_id']] = fn_find_promotion_condition($promotion['conditions'], 'limit_discount_bonus_by_amount_packages');
        }
        $limit_discount_bonus_condition = &$cart['limit_discount_bonus_by_amount_packages'][$bonus['promotion_id']];

        if (!empty($limit_discount_bonus_condition)) {
            foreach ($cart['products'] as $k => $v) {
                if (!empty($v['extra']['limit_discount_bonus_by_amount_packages'])) {
                    $cart['products'][$v['extra']['limit_discount_bonus_by_amount_packages']]['amount'] += $v['amount'];
                    if (fn_delete_cart_product($cart, $k)) unset($cart_products[$k]);
                }
            }
        }

        $sort = array_column($cart['products'], 'timestamp', 'item_id');
        asort($sort);
        $cart_products = fn_array_merge($sort, $cart_products);
        foreach (fn_array_merge($sort, $cart_products) as $k => $v) {
            if (isset($v['exclude_from_calculate']) || (!floatval($v['base_price']) && $v['base_price'] != 0)) {
                continue;
            }

            $valid = false;

            if ($bonus['bonus'] == 'discount_on_products_from_conditions') {
                $valid = fn_promotion_validate_attribute($v['product_id'], $bonus['value'], 'in') && fn_promotion_validate_attribute($v['product_id'], array_column($condition_products, 'product_id'), 'in');
            } elseif ($bonus['bonus'] == 'static_discount_on_products_from_conditions') {
                $valid = fn_promotion_validate_attribute($v['product_id'], array_column($condition_products, 'product_id'), 'in');
            }

            if ($valid && !empty($limit_discount_bonus_condition)) {
                // override by fn_promotion_validate_attribute
                if ($limit_discount_bonus_condition['value'] >= $cart['products'][$k]['amount']/$v['items_in_package']) {
                    $limit_discount_bonus_condition['value'] -= $cart['products'][$k]['amount']/$v['items_in_package'];
                } elseif ($limit_discount_bonus_condition['value'] > 0 && $limit_discount_bonus_condition['value'] >= max($v['qty_step'], $v['min_qty'])/$v['items_in_package']) {

                    // we should divide sku into two rows
                    $first_amount = $limit_discount_bonus_condition['value'] * $v['items_in_package'];
                    $first_amount -= fmod($first_amount, max($v['qty_step'], $v['min_qty']));

                    if ($first_amount) {
                        $product_data = array (
                            $v['product_id'] => array (
                                'amount' => $cart['products'][$k]['amount'] - $first_amount,
                                'product_id' => $v['product_id'],
                                'extra' => $cart['products'][$k]['extra']
                            ),
                        );
                        $product_data[$v['product_id']]['extra']['limit_discount_bonus_by_amount_packages'] = $k;

                        $cart['products'][$k]['amount'] = $first_amount;
                        $existing_products = array_keys($cart['products']);

                        if ($ids = fn_add_product_to_cart($product_data, $cart, $auth)) {

                            $new_products = array_diff(array_keys($cart['products']), $existing_products);

                            if (!empty($new_products)) {
                                $hash = array_pop($new_products);
                            } else {
                                $hash = key($ids);
                            }

                            $_cproduct = fn_get_cart_product_data($hash, $cart['products'][$hash], true, $cart, $auth, !empty($new_products) ? 0 : $p_data['amount']);
                            if (!empty($_cproduct)) {
                                $cart_products[$hash] = $_cproduct;
                            }
                        }

                        $cart['recalculate'] = true;
                        fn_save_cart_content($cart, $auth['user_id']);
                        $limit_discount_bonus_condition['value'] -= $first_amount/$v['items_in_package'];
                    } else {
                        $valid = false;
                    }
                } else {
                    $valid = false;
                }
            }

            if ($valid) {
                if (!isset($cart_products[$k]['promotions'])) {
                    $cart_products[$k]['promotions'] = array();
                }

                if (isset($cart['products'][$k]['extra']['promotions'][$bonus['promotion_id']])) {
                    $cart_products[$k]['promotions'][$bonus['promotion_id']] = $cart['products'][$k]['extra']['promotions'][$bonus['promotion_id']];
                }

                if (!isset($cart_products[$k]['promotions'][$bonus['promotion_id']])
                    && fn_promotion_apply_discount($bonus['promotion_id'], $bonus, $cart_products[$k], true, $cart, $cart_products)
                ) {
                    $cart['use_discount'] = true;
                }
            }
        }
    }

    return true;
}

function fn_category_promotion_generate_cart_id(&$_cid, $extra) {
    if (!empty($extra['limit_discount_bonus_by_amount_packages'])) {
        $_cid['limit_discount_bonus_by_amount_packages'] = $extra['limit_discount_bonus_by_amount_packages'];
    }
}

function fn_category_promotion_promotion_apply_pre($promotions, $zone, &$data, $auth, $cart_products) {
    unset($data['limit_discount_bonus_by_amount_packages']);
}
