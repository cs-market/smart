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
use Tygh\Enum\SiteArea;
use Tygh\Enum\YesNo;
use Tygh\Enum\ProductTracking;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_get_storages($params = [], $items_per_page = 0) {
    if (empty(Tygh::$app['session']['auth']['user_id'])) {
        return [false, false];
    }

    $condition = $join = '';

    if (SiteArea::isStorefront(AREA)) {
        $params['usergroup_ids'] = Tygh::$app['session']['auth']['usergroup_ids'];
        if (!empty(Tygh::$app['session']['auth']['company_id'])) {
            $params['company_id'] = Tygh::$app['session']['auth']['company_id'];
        }
    }

    if (!empty($params['status'])) {
        $condition .= db_quote(' AND ?:storages.status = ?s', $params['status']);
    }

    if (Registry::get('runtime.company_id')) {
        $params['company_id'] = Registry::get('runtime.company_id');
    }

    if (isset($params['company_id'])) {
        $condition .= db_quote(" AND ?:storages.company_id = ?i", $params['company_id']);
    }

    if (isset($params['storage_id'])) {
        $condition .= db_quote(' AND ?:storages.storage_id = ?i', $params['storage_id']);
    }

    if (!empty($params['storage_ids'])) {
        if (!is_array($params['storage_ids'])) {
            $params['storage_ids'] = explode(',', $params['storage_ids']);
        }
        $condition .= db_quote(' AND storage_id IN (?a)', $params['storage_ids']);
    }

    if (!empty($params['usergroup_ids'])) {
        $join .= db_quote('LEFT JOIN ?:storage_usergroups ON ?:storages.storage_id = ?:storage_usergroups.storage_id');
        $condition .= db_quote(' AND (?:storage_usergroups.usergroup_id IN (?a) OR ?:storage_usergroups.usergroup_id IS NULL)', $params['usergroup_ids']);
    }

    fn_set_hook('get_storages', $params, $join, $condition);

    $storages = db_get_hash_array("SELECT ?:storages.* FROM ?:storages $join WHERE 1 ?p", 'storage_id', $condition);

    if (isset($params['storage_id']) || (isset($params['get_usergroups']) && $params['get_usergroups'] === 'true')) {
        foreach ($storages as &$storage) {
            $storage['usergroup_ids'] = db_get_fields('SELECT usergroup_id FROM ?:storage_usergroups WHERE storage_id = ?i', $storage['storage_id']);
        }
    }

    fn_set_hook('get_storages_post', $storages, $params);

    return [$storages, $params];
}

function fn_update_storage($storage_data, $storage_id = 0) {
    unset($storage_data['storage_id']);

    if (fn_allowed_for('MULTIVENDOR') && Registry::get('runtime.company_id')) {
        $storage_data['company_id'] = $storage_data['company_id'] ?? Registry::get('runtime.company_id');
        if (!empty($storage_id) && !($storage_id = db_get_field('SELECT storage_id FROM ?:storages WHERE storage_id = ?i AND company_id = ?i', $storage_id, $storage_data['company_id']))) {
            return false;
        }
    }

    fn_set_hook('update_storage_pre', $storage_data, $storage_id);

    if (!empty($storage_id)) {
        db_query("UPDATE ?:storages SET ?u WHERE storage_id = ?i", $storage_data, $storage_id);
    } else {
        $storage_data['storage_id'] = $storage_id = db_query("INSERT INTO ?:storages ?e", $storage_data);
    }

    if (isset($storage_data['usergroup_ids'])) {
        db_query("DELETE FROM ?:storage_usergroups WHERE storage_id = ?i", $storage_id);
        $storage_data['usergroup_ids'] = empty($storage_data['usergroup_ids']) ? [0] : $storage_data['usergroup_ids'];
        $usergroups_data = [];

        fn_set_hook('update_storage_usergroups_pre', $storage_data);

        foreach ($storage_data['usergroup_ids'] as $usergroup_id) {
            $usergroups_data[] = ['storage_id' => $storage_id, 'usergroup_id' => $usergroup_id];
        }
        if ($usergroups_data) db_query('INSERT INTO ?:storage_usergroups ?m', $usergroups_data);
    }

    return $storage_id;
}

function fn_delete_storages($storage_ids) {
    $res = false;
    if (!is_array($storage_ids)) {
        $storage_ids = explode(',', $storage_ids);
    }

    if (Registry::get('runtime.company_id')) {
        $storage_ids = db_get_fields('SELECT storage_id FROM ?:storages WHERE company_id = ?i AND storage_id IN (?a)', Registry::get('runtime.company_id'), $storage_ids);
    }

    if (!empty($storage_ids)) {
        $res = db_query("DELETE FROM ?:storages WHERE storage_id IN (?n)", $storage_ids);
        db_query("DELETE FROM ?:storages_products WHERE storage_id IN (?n)", $storage_ids);
        db_query("DELETE FROM ?:storage_usergroups WHERE storage_id IN (?n)", $storage_ids);

        fn_set_hook('delete_storages', $storage_ids);
    }

    return $res;
}

function fn_storages_get_usergroups_pre(&$params, $lang_code) {
    if (fn_allowed_for('MULTIVENDOR')) {
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $usergroup_ids = db_get_field("SELECT usergroup_ids FROM ?:vendor_plans LEFT JOIN ?:companies ON ?:companies.plan_id = ?:vendor_plans.plan_id WHERE company_id = ?i", $params['company_id']);
            if (!empty($usergroup_ids)) $params['usergroup_id'] = explode(',',$usergroup_ids);
        }
    }
}

function fn_storages_update_product_post($product_data, $product_id, $lang_code, $create) {
    if (isset($product_data['storages'])) {
        db_query('DELETE FROM ?:storages_products WHERE product_id = ?i', $product_id);
        $update = [];
        foreach ($product_data['storages'] as $storage_id => &$storage_data) {
            if (isset($storage_data['storage_id'])) $storage_id = $storage_data['storage_id'];
            if (!empty(array_filter($storage_data))) {
                $update[$storage_id] = $storage_data;
                $update[$storage_id]['storage_id'] = $storage_id;
                $update[$storage_id]['product_id'] = $product_id;
            }
        }

        if (!empty($update)) db_query('INSERT INTO ?:storages_products ?m', $update);
    }
}

function fn_get_storages_amount($product_id) {
    $return = [];
    if ($product_id) {
        $return = db_get_hash_array('SELECT * FROM ?:storages_products WHERE product_id = ?i', 'storage_id', $product_id);
    }

    return $return;
}

function fn_storages_get_product_data($product_id, &$field_list, &$join, $auth, $lang_code, &$condition, &$price_usergroup) {
    if ($storage = Registry::get('runtime.current_storage')) {
        $usergroup_ids = !empty($auth['usergroup_ids']) ? $auth['usergroup_ids'] : array();
        $usergroup_ids = array_intersect($usergroup_ids, $storage['usergroup_ids']);
        $price_usergroup = db_quote(' 
            AND CASE WHEN 
            (SELECT count(*) FROM ?:product_prices WHERE product_id = ?i AND cscart_product_prices.usergroup_id IN (?a) )
            THEN ?:product_prices.usergroup_id IN (?a) 
            ELSE ?:product_prices.usergroup_id = ?i END', $product_id, array_diff($usergroup_ids, [USERGROUP_ALL]), array_diff($usergroup_ids, [USERGROUP_ALL]), USERGROUP_ALL);
        $field_list .= db_quote(', ?:storages_products.amount, ?:storages_products.min_qty, ?:storages_products.qty_step');
        $join .= db_quote(' LEFT JOIN ?:storages_products ON ?:storages_products.product_id = ?i AND ?:storages_products.storage_id = ?i', $product_id, $storage['storage_id']);
    }
}

function fn_storages_load_products_extra_data_post(&$products, $product_ids) {
    // а что если сделать в load_products_extra_data нормально?
    if ($storage = Registry::get('runtime.current_storage')) {
        $usergroup_ids = Tygh::$app['session']['auth']['usergroup_ids'];
        $usergroup_ids = array_intersect($usergroup_ids, $storage['usergroup_ids']);
        $usergroup_ids = array_filter($usergroup_ids);

        if ($usergroup_ids) {
            $prices = db_get_hash_array("SELECT prices.product_id, IF(prices.percentage_discount = 0, prices.price, prices.price - (prices.price * prices.percentage_discount)/100) as price FROM ?:product_prices prices WHERE product_id IN (?a) AND lower_limit = ?i AND usergroup_id IN (?a)", 'product_id', $product_ids, 1, $usergroup_ids);
            $products = fn_array_merge($products, $prices);
        }
    }
}

function fn_storages_load_products_extra_data(&$extra_fields, $products, $product_ids, $params, $lang_code) {
    if ($storage = Registry::get('runtime.current_storage')) {
        $extra_fields['?:storages_products'] = [
            'primary_key' => 'product_id',
            'fields' => [
                'amount', 'min_qty', 'qty_step'
            ],
            'condition' => db_quote(' AND ?:storages_products.storage_id = ?i', $storage['storage_id'])
        ];
    }
}

function fn_storages_get_products(array &$params, array &$fields, array &$sortings, &$condition, &$join, $sorting, $group_by, $lang_code, $having)
{
    if (strpos($condition, 'products.amount')) {
        // когда $params['amount_from']
//        $condition = str_replace(
//            'products.amount',
//            db_quote(
//                '(CASE WHEN (SELECT count(*) FROM ?:storages_products WHERE product_id = ?i)'
//                . ' THEN warehouses_destination_products_amount.amount'
//                . ' ELSE products.amount END)',
//                YesNo::YES
//            ),
//            $condition
//        );
//        CASE WHEN
//        (SELECT count(*) FROM ?:product_prices WHERE product_id = ?i AND cscart_product_prices.usergroup_id IN (?a) )
//            THEN ?:product_prices.usergroup_id IN (?a)
//            ELSE ?:product_prices.usergroup_id = ?i END
    }

//    $check_storage_product_amount = SiteArea::isStorefront($params['area']) && (
//            (
//                Registry::get('settings.General.inventory_tracking') !== YesNo::NO
//                && Registry::get('settings.General.show_out_of_stock_products') === YesNo::NO
//            )
//            || (isset($params['amount_from']) && fn_is_numeric($params['amount_from']))
//            || (isset($params['amount_to']) && fn_is_numeric($params['amount_to']))
//        );
//
//    if ($check_storage_product_amount) {
//
//        $join .= db_quote(
//            ' LEFT JOIN ?:warehouses_destination_products_amount AS warehouses_destination_products_amount'
//            . ' ON warehouses_destination_products_amount.product_id = products.product_id'
//            . ' AND warehouses_destination_products_amount.destination_id = ?i'
//            . ' AND warehouses_destination_products_amount.storefront_id = ?i',
//            $destination_id,
//            $storefront_id
//        );
//
//        // FIXME Dirty hack

//    }
}

function fn_init_storages() {
    if (AREA != 'C') {
        return array(INIT_STATUS_OK);
    }

    $storages = Registry::getOrSetCache(
        'fn_get_storages',
        ['storages', 'storage_usergroups'],
        'user',
        static function () {
            list($storages) = fn_get_storages(['get_usergroups' => true]);
            return $storages;
        }
    );

    if (!empty($storages)) {
        if (!empty($_REQUEST['storage']) && !empty($storages[$_REQUEST['storage']])) {
            $storage = $_REQUEST['storage'];

        } elseif (($s = fn_get_session_data('storage')) && !empty($storages[$s])) {
            $storage = $s;
        } else {
            reset($storages);
            $storage = key($storages);
            fn_set_notification('N', __('notice'), __('storages.force_to_choose_storage'));
        }

        if ($storage != fn_get_session_data('storage')) {
            fn_set_session_data('storage', $storage, COOKIE_ALIVE_TIME);
        }

        Registry::set('runtime.current_storage', $storages[$storage]);
        Registry::set('runtime.storages', $storages);

        fn_define('STORAGE', $storage);

        Tygh::$app['view']->assign('storages', $storages);
    }

    return array(INIT_STATUS_OK);
}

function fn_storages_pre_add_to_cart(&$product_data, $cart, $auth, $update) {
    if ($update) {
        foreach ($product_data as $key => &$data) {
            $data['extra']['storage_id'] = $cart['products'][$key]['extra']['storage_id'];
        }
        unset($data);
    } elseif ($storage_id = Registry::ifGet('runtime.current_storage.storage_id', false)) {
        foreach ($product_data as $key => &$data) {
            $data['extra']['storage_id'] = $storage_id;
        }
        unset($data);
    }
}

function fn_storages_add_product_to_cart_get_price($product_data, $cart, $auth, $update, $_id, $data, $product_id, $amount, &$price, $zero_price_action, $allow_add) {
    // if we have the storages for current user

    if ($storages = Registry::get('runtime.storages')) {
        $usergroup_ids = $auth['usergroup_ids'];

        $storage = $storages[$data['extra']['storage_id']];
        $usergroup_ids = array_intersect($usergroup_ids, $storage['usergroup_ids']);
        $usergroup_ids = array_filter($usergroup_ids);
        if ($usergroup_ids) {
            $price = db_get_field("SELECT IF(prices.percentage_discount = 0, prices.price, prices.price - (prices.price * prices.percentage_discount)/100) as price FROM ?:product_prices prices WHERE product_id = ?i AND lower_limit = ?i AND usergroup_id IN (?a)", $product_id, 1, $usergroup_ids);
        }
    }
}

function fn_storages_pre_get_cart_product_data($hash, $product, $skip_promotion, $cart, $auth, $promotion_amount, &$fields, &$join, $params) {
    if ($storages = Registry::get('runtime.storages') && !empty($product['extra']['storage_id'])) {
        $fields[] = '?:storages_products.qty_step';
        $fields[] = '?:storages_products.min_qty';
        $join .= db_quote(' LEFT JOIN ?:storages_products ON ?:storages_products.product_id = ?:products.product_id AND ?:storages_products.storage_id = ?i', $product['extra']['storage_id']);
    }
}

function fn_storages_get_cart_product_data($product_id, &$_pdata, $product, $auth, $cart, $hash) {
    if ($storages = Registry::get('runtime.storages')) {
        $usergroup_ids = $auth['usergroup_ids'];

        $storage = $storages[$product['extra']['storage_id']];
        $usergroup_ids = array_intersect($usergroup_ids, $storage['usergroup_ids']);
        $usergroup_ids = array_filter($usergroup_ids);
        if ($usergroup_ids) {
            $_pdata['price'] = db_get_field("SELECT IF(prices.percentage_discount = 0, prices.price, prices.price - (prices.price * prices.percentage_discount)/100) as price FROM ?:product_prices prices WHERE product_id = ?i AND lower_limit = ?i AND usergroup_id IN (?a)", $product_id, 1, $usergroup_ids);
        }
        $_pdata['storage_id'] = $product['extra']['storage_id'];
    }
}

function fn_storages_generate_cart_id(&$_cid, $extra) {
    if ($extra['storage_id']) {
        $_cid['storage_id'] = $extra['storage_id'];
    }
}

function fn_storages_check_amount_in_stock_before_check($product_id, $amount, $product_options, $cart_id, $is_edp, $original_amount, $cart, $update_id, &$product, &$current_amount) {

    $storage_id = $cart['products'][$cart_id]['extra']['storage_id'] ?? Registry::ifGet('runtime.current_storage.storage_id', false);

    if ($storage_id) {
        $product = db_get_row(
            'SELECT p.tracking, s.amount, s.min_qty, p.max_qty, s.qty_step, p.list_qty_count, p.out_of_stock_actions, p.product_type, pd.product'
            . ' FROM ?:products AS p'
            . ' LEFT JOIN ?:product_descriptions AS pd ON pd.product_id = p.product_id AND lang_code = ?s'
            . ' LEFT JOIN ?:storages_products AS s ON s.product_id = p.product_id AND storage_id = ?i'
            . ' WHERE p.product_id = ?i',
            CART_LANGUAGE,
            $storage_id,
            $product_id
        );

        $product = fn_normalize_product_overridable_fields($product);

        if (
            isset($product['tracking'])
            && $product['tracking'] !== ProductTracking::DO_NOT_TRACK
            && Registry::get('settings.General.inventory_tracking') !== YesNo::NO
        ) {
            $current_amount = $product['amount'];

            if (!empty($cart['products']) && is_array($cart['products'])) {
                $product_not_in_cart = true;
                foreach ($cart['products'] as $k => $v) {
                    // Check if the product with the same selectable options already exists ( for tracking = O)
                    if ($k != $cart_id) {
                        if (
                            isset($product['tracking'])
                            && ($product['tracking'] !== ProductTracking::DO_NOT_TRACK && (int)$v['product_id'] === (int)$product_id && $v['extra']['storage_id'] === $storage_id)
                        ) {
                            $current_amount -= $v['amount'];
                        }
                    } else {
                        $product_not_in_cart = false;
                    }
                }

                if (
                    $product['tracking'] !== ProductTracking::DO_NOT_TRACK
                    && !empty($update_id)
                    && $product_not_in_cart
                    && !empty($cart['products'][$update_id])
                ) {
                    $current_amount += $cart['products'][$update_id]['amount'];
                }
            }
        }
    }
}

function fn_storages_shippings_group_products_list(&$products, &$groups) {
    if ($storages = Registry::get('runtime.storages')) {
        $stored_storages_ids = array();

        $suppliers = array();
        $storages_groups = array();
        foreach ($groups as $group) {
            foreach ($group['products'] as $cart_id => $product) {
                $storage_id = $product['storage_id'];
                $storages_group_key = $storage_id ? $group['company_id'] . "_" . $storage_id : $group['company_id'];

                if (empty($storages_groups[$storages_group_key]) && $storage_id) {
                    $storages_groups[$storages_group_key] = $group;
                    $storages_groups[$storages_group_key]['storage_id'] = $storage_id;

                    $storages_groups[$storages_group_key]['name'] = Registry::get("runtime.storages.$storage_id.storage");

                    $storages_groups[$storages_group_key]['products'] = array();
                }

                if (empty($storages_groups[$storages_group_key]) && !$storage_id) {
                    $storages_groups[$storages_group_key] = $group;
                    $storages_groups[$storages_group_key]['products'] = array();
                }

                $storages_groups[$storages_group_key]['products'][$cart_id] = $product;
                $storages_groups[$storages_group_key]['group_key'] = $storages_group_key;
            }
        }

        ksort($storages_groups);
        $groups = array_values($storages_groups);
    }
}

function fn_storages_pre_update_order(&$cart, $order_id) {
    if (Registry::get('runtime.storages') && count($cart['product_groups']) == 1) {
        $cart['storage_id'] = $cart['product_groups'][0]['storage_id'];
    }
}

function fn_storages_update_product_amount_pre($product_id, $amount_delta, $product_options, $sign, $tracking, &$current_amount, $product_code, $notify, $order_info, $cart_id) {
    if ($order_info['storage_id']) {
        $current_amount = db_get_field('SELECT amount FROM ?:storages_products WHERE storage_id = ?i AND product_id = ?i', $order_info['storage_id'], $product_id);
    }
}

function fn_storages_update_product_amount($new_amount, $product_id, $cart_id, $tracking, $notify, $order_info, $amount_delta, $current_amount, $original_amount, $sign) {
    if ($order_info['storage_id']) {
        db_query('UPDATE ?:storages_products SET amount = ?d WHERE product_id = ?i AND storage_id = ?i', $new_amount, $product_id, $order_info['storage_id']);
    }
}

function fn_storages_get_order_info(&$order, $additional_data) {
    if (!empty($order['storage_id'])) {
        list($storages,) = fn_get_storages(['storage_id' => $order['storage_id']]);
        $order['storage'] = reset($storages);
    }
}

function fn_storages_monolith_generate_xml($order_info, $monolith_order, &$d_record) {
    if (!empty($order_info['storage'])) {
        $d_record[3] = $order_info['storage']['code'];
    }
}
