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

use Tygh\Registry;
use Tygh\Enum\YesNo;

defined('BOOTSTRAP') or die('Access denied');

function fn_get_product_groups($params) {
    $condition = '';
    if (!empty($params['status'])) {
        $condition .= db_quote(' AND status = ?s', $params['status']);
    }

    if (Registry::get('runtime.company_id')) {
        $params['company_id'] = Registry::get('runtime.company_id');
    }

    if (isset($params['company_id'])) {
        $condition .= db_quote(" AND company_id = ?i", $params['company_id']);
    }

    if (isset($params['group_id'])) {
        $condition .= db_quote(' AND group_id = ?i', $params['group_id']);
    }
    if (!empty($params['group_ids'])) {
        if (!is_array($params['group_ids'])) {
            $params['group_ids'] = explode(',', $params['group_ids']);
        }
        $condition .= db_quote(' AND group_id IN (?a)', $params['group_ids']);
    }

    $product_groups = db_get_hash_array("SELECT * FROM ?:product_groups WHERE 1 ?p", 'group_id', $condition);

    return $product_groups;
}

function fn_get_product_group_name($group_id)
{
    if (!empty($group_id)) {
        $group_name = db_get_field("SELECT ?:product_groups.group FROM ?:product_groups WHERE ?:product_groups.group_id = ?i", $group_id);
    }

    return !empty($group_name) ? $group_name : __('none');
}

function fn_update_product_groups($group_data, $group_id = 0) {
    if (!empty($group_id)) {
        db_query("UPDATE ?:product_groups SET ?u WHERE group_id = ?i", $group_data, $group_id);
    } else {
        $group_data['group_id'] = $group_id = db_query("INSERT INTO ?:product_groups ?e", $group_data);
    }
}

function fn_delete_product_group($group_id) {
    $result = db_query("DELETE FROM ?:product_groups WHERE group_id = ?i", $group_id);
    return $result;
}

function fn_product_groups_calculate_cart_items(&$cart, $cart_products, $auth, $apply_cart_promotions) {
    foreach ($cart_products as $cart_id => $product) {
        if (isset($product['group_id'])) {
            $cart['products'][$cart_id]['group_id'] = $product['group_id'];
        }
    }

    if ($cart['recalculate'] == true) {
        $group_ids = array_unique(fn_array_column($cart_products, 'group_id'));
        $cart['groups'] = fn_get_product_groups(array('group_ids' => $group_ids));
    }
}

function fn_product_groups_pre_get_cart_product_data($hash, $product, $skip_promotion, $cart, $auth, $promotion_amount, &$fields, $join) {
    $fields[] = 'group_id';
}

function fn_product_groups_get_cart_product_data_post_options($product_id, $_pdata, &$product) {
    $product['group_id'] = $_pdata['group_id'] ?? 0;
}

function fn_product_groups_get_groups_from_products($products) {
    $groups = [];
    if (!empty($products) && $group_ids = array_filter(array_unique(fn_array_column($products, 'group_id')))) {
        $groups = fn_get_product_groups(array('group_ids' => $group_ids));
    }

    $free_products = array_filter($products, function($v) {
        return !$v['price'] && !empty($v['extra']['exclude_from_calculate']);
    });

    if (!empty($free_products)) {
        $companies = array_unique(fn_array_column($free_products, 'company_id'));
        $separate_zero_products_for_companies = db_get_fields('SELECT company_id FROM ?:companies WHERE company_id IN (?a) AND separate_zero_products = ?s', $companies, YesNo::YES);
        $free_products = array_filter($free_products, function($v) use ($separate_zero_products_for_companies) {
            return in_array($v['company_id'], $separate_zero_products_for_companies);
        });
        $free_group_ids = array_unique(fn_array_column($free_products, 'group_id'));
        foreach ($free_group_ids as $free_group_id) {
            $groups[$free_group_id."_free"] = $groups[$free_group_id];
            $groups[$free_group_id."_free"]['mandatory_order_split'] = YesNo::YES;
        }

        foreach ($free_products as $cart_id => $product) {
            $products[$cart_id]['group_id'] .= '_free';
        }
    }

    return [$groups, $products];
}

function fn_product_groups_get_package_info(&$group) {
    if (is_array($group['products'])) {
        $weight = db_get_hash_single_array('SELECT product_id, weight FROM ?:products WHERE product_id IN (?a)', ['product_id', 'weight'], array_column($group['products'], 'product_id'));

        $group['package_info'] = [
            'C' => 0,
            'W' => 0,
            'I' => 0,
            'shipping_freight' => 0,
        ];
        foreach ($group['products'] as $key_product => $product) {
            if (($product['is_edp'] == 'Y' && $product['edp_shipping'] != 'Y') ||
            (!empty($product['free_shipping']) && $product['free_shipping'] == 'Y')
            ) {
                continue;
            }
            if (!empty($product['exclude_from_calculate'])) {
                $product_price = 0;
            } elseif (!empty($product['subtotal'])) {
                $product_price = $product['subtotal'];
            } elseif (!empty($product['price'])) {
                $product_price = $product['price'];
            } elseif (!empty($product['base_price'])) {
                $product_price = $product['base_price'];
            } else {
                $product_price = 0;
            }
            if (!isset($group['subtotal'])) $group['subtotal'] = 0;
            $group['subtotal'] += $product['price'] * $product['amount'];

            if (!(!empty($product['free_shipping']) && $product['free_shipping'] == 'Y')) {
                $product['weight'] = !empty($product['weight']) ? $product['weight'] : $weight[$product['product_id']];
                $group['package_info']['C'] += $product_price * $product['amount'];
                $group['package_info']['W'] += !empty($product['weight']) ? $product['weight'] * $product['amount'] : 0;
                $group['package_info']['I'] += $product['amount'];

                if (isset($product['shipping_freight'])) {
                    $group['package_info']['shipping_freight'] += $product['shipping_freight'] * $product['amount'];
                }
            }
        }
    }
}

function fn_product_groups_split_cart($cart, $only_mandatory_order_split = false) {
    $p_groups = array();
    if (!fn_cart_is_empty($cart)) {
        foreach ($cart['product_groups'] as $group_key => $group_data) {
            if (list($groups, $group_products) = fn_product_groups_get_groups_from_products($group_data['products'])) {
                foreach ($groups as $group_id => $group) {
                    if (!(YesNo::toBool($group['mandatory_order_split']) || (isset($cart['split_order']) && YesNo::toBool($cart['split_order'][$group_id]) && !$only_mandatory_order_split))) continue;

                    $proto = $group_data;
                    $proto['group_id'] = $group['group_id'];
                    $proto['subtotal'] = 0;
                    $proto['group'] = $group;
                    $proto['name'] .= ' (' . $group['group'] . ')';
                    $proto['products'] = [];

                    foreach ($group_products as $cart_id => $product) {
                        // string "(int)_free" can be converted to (int) during comparison
                        if ((string) $group_id == (string) $product['group_id']) {
                            $proto['products'][$cart_id] = $product;
                            unset($group_products[$cart_id]);
                        }
                    }
                    fn_product_groups_get_package_info($proto);
                    $p_groups[] = $proto;
                }
            }

            if (!empty($group_products)) {
                $group_data['products'] = $group_products;
                fn_product_groups_get_package_info($group_data);
                $p_groups[] = $group_data;
            }
        }
    }

    return $p_groups;
}

function fn_product_groups_calculate_cart_post(&$cart, $auth, $calculate_shipping, $calculate_taxes, $options_style, $apply_cart_promotions, $cart_products, $product_groups) {
    unset($cart['ask_to_split_order']);
    if (list($groups) = fn_product_groups_get_groups_from_products($cart['products'])) {
        foreach ($groups as $group) {
            if (!YesNo::toBool($group['mandatory_order_split'])) {
                $cart['ask_to_split_order'][$group['group_id']] = $group['group'];
            }
        }
    }
}

function fn_product_groups_pre_update_order(&$cart, $order_id = 0) {
    if (empty($cart['parent_order_id'])) $cart['product_groups'] = fn_product_groups_split_cart($cart);
    if (count($cart['product_groups']) == 1) {
        $cart['group_id'] = isset(reset($cart['product_groups'])['group_id']) ? reset($cart['product_groups'])['group_id'] : 0;
    }
}

function fn_exim_import_product_group($group) {
    return db_get_field('SELECT `group_id` FROM ?:product_groups WHERE `group` = ?s', $group) ?? 0;
}

function fn_exim_get_product_group($group_id) {
    return db_get_field('SELECT `group` FROM ?:product_groups WHERE group_id = ?i', $group_id);
}

function fn_product_groups_place_suborders_pre($order_id, $cart, $auth, $action, $issuer_id, &$suborder_cart, $key_group, $group) {
    $suborder_cart['group_id'] = $group['group_id'] ? : 0;
}

function fn_product_groups_place_suborders($cart, &$suborder_cart, $key_group) {
    $suborder_cart['products'] = $cart['product_groups'][$key_group]['products'];

    foreach ($suborder_cart['promotions'] as $promotion_id => $promo_data) {
        if (!empty($promo_data['bonuses'])) {
            $found = false;
            foreach ($promo_data['bonuses'] as $bonus) {
                if (in_array($bonus['bonus'], ['free_products', 'promotion_step_free_products'])) {
                    $products = array_column($bonus['value'], 'product_id');
                    $cart_products = array_filter($suborder_cart['products'], function($v) {
                        return !$v['price'];
                    });
                    $cart_products = array_column($cart_products, 'product_id');
                    foreach ($products as $pid) {
                        $found = ($found || in_array($pid, $cart_products));
                    }
                }
            }
            if (!$found) {
                unset($suborder_cart['promotions'][$promotion_id]);
            }
        }
    }
}

function fn_product_groups_get_product_fields(&$fields) {
    $fields[] = array('name' => '[data][group_id]', 'text' => __('product_group'));
}

function fn_product_groups_pre_get_orders($params, &$fields, $sortings, $get_totals, $lang_code) {
    $fields[] = '?:orders.group_id';
}

function fn_product_groups_get_products($params, $fields, $sortings, &$condition, $join, $sorting, $group_by, $lang_code, $having) {
    if (isset($params['group_id']) && !empty($params['group_id'])) {
        $condition .= db_quote(' AND products.group_id = ?i', $params['group_id']);
    }
}

function fn_product_groups_exim1c_order_xml_pre(&$order_xml, $order_data, $cml) {
    if (isset($order_data['group_id']) && !empty($order_data['group_id'])) {
        $order_xml[$cml['value_fields']][][$cml['value_field']] = array(
            $cml['name'] => $cml['product_group'],
            $cml['value'] => db_get_field('SELECT `group` FROM ?:product_groups WHERE group_id = ?i', $order_data['group_id'])
        );
    }
}
