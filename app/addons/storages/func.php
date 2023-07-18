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

defined('BOOTSTRAP') or die('Access denied');

function fn_storages_install() {
    if (Registry::get('addons.user_price.status') == 'A') {
        db_query("ALTER TABLE ?:user_price ADD storage_id mediumint UNSIGNED NOT NULL DEFAULT '0' AFTER user_id");
        db_query("ALTER TABLE ?:user_price DROP PRIMARY KEY, ADD PRIMARY KEY (`product_id`, `user_id`, `storage_id`)"); 
    }
}

function fn_storages_uninstall() {
    if (Registry::get('addons.user_price.status') == 'A') {
        db_query("ALTER TABLE ?:user_price DROP PRIMARY KEY, ADD PRIMARY KEY (`product_id`, `user_id`)");
        db_query("ALTER TABLE ?:user_price DROP `storage_id`");
    }
}

function fn_get_storages($params = [], $items_per_page = 0) {
    if (empty(Tygh::$app['session']['auth']['user_id'])) {
        return [false, false];
    }

    $default_params = [
        'page'           => 1,
        'items_per_page' => $items_per_page
    ];

    $params = array_merge($default_params, $params);

    $condition = $join = '';

    if (SiteArea::isStorefront(AREA)) {
        $params['usergroup_ids'] = Tygh::$app['session']['auth']['usergroup_ids'];
        if (!empty(Tygh::$app['session']['auth']['company_id'])) {
            $params['company_id'] = Tygh::$app['session']['auth']['company_id'];
        }
        $params['status'] = 'A';
    }

    if (!empty($params['status'])) {
        $condition .= db_quote(' AND ?:storages.status = ?s', $params['status']);
    }

    if (Registry::get('runtime.company_id')) {
        $params['company_id'] = Registry::get('runtime.company_id');
    }

    if (!empty($params['company_id'])) {
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

    if (!empty($params['code'])) {
        $condition .= db_quote(' AND ?:storages.code = ?s', $params['code']);
    }

    if (!empty($params['usergroup_ids'])) {
        $join .= db_quote('LEFT JOIN ?:storage_usergroups ON ?:storages.storage_id = ?:storage_usergroups.storage_id');
        $condition .= db_quote(' AND (?:storage_usergroups.usergroup_id IN (?a) OR ?:storage_usergroups.usergroup_id IS NULL)', $params['usergroup_ids']);
    }

    if (!empty($params['q'])) {
        $params['q'] = trim($params['q']);
        $condition .= db_quote(' AND (?:storages.storage LIKE ?l OR ?:storages.code LIKE ?l)', '%'.$params['q'].'%', '%'.$params['q'].'%');
    }

    fn_set_hook('get_storages', $params, $join, $condition);

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field(
            'SELECT COUNT(*) FROM ?:storages ?p WHERE 1 ?p ?p',
            $join,
            $condition
        );
        $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
    }

    $storages = db_get_hash_array("SELECT ?:storages.* FROM ?:storages $join WHERE 1 ?p ?p", 'storage_id', $condition, $limit);

    if (isset($params['storage_id']) || (isset($params['get_usergroups']) && $params['get_usergroups'] === 'true')) {
        $storages_usergroups = db_get_array('SELECT storage_id, usergroup_id FROM ?:storage_usergroups WHERE storage_id IN (?a)', array_keys($storages));

        foreach ($storages as &$storage) {
            $storage_usergroups = array_filter($storages_usergroups, function($v) use ($storage) {
                return ($v['storage_id'] == $storage['storage_id']);
            });

            $storage['usergroup_ids'] = array_column($storage_usergroups, 'usergroup_id');
        }
    }

    fn_set_hook('get_storages_post', $storages, $params);

    return [$storages, $params];
}

function fn_update_storage($storage_data, $storage_id = 0) {
    unset($storage_data['storage_id']);

    if (fn_allowed_for('MULTIVENDOR') && Registry::get('runtime.company_id')) {
        $storage_data['company_id'] = Registry::get('runtime.company_id');
        if (!empty($storage_id) && !(db_get_field('SELECT storage_id FROM ?:storages WHERE storage_id = ?i AND company_id = ?i', $storage_id, $storage_data['company_id']))) {
            fn_set_notification('E', __('error'), __('storages.access_denied'));
            return false;
        }

        $db_storage_id = db_get_field('SELECT storage_id FROM ?:storages WHERE code = ?s AND company_id = ?i', $storage_data['code'], $storage_data['company_id']);
        if ($db_storage_id && $db_storage_id != $storage_id) {
            fn_set_notification('E', __('error'), __('storages.storage_already_exist'));
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

function fn_storages_update_product_post($product_data, $product_id, $lang_code, $create) {
    if (isset($product_data['storages'])) {
        db_query('DELETE FROM ?:storages_products WHERE product_id = ?i', $product_id);
        $product_data['storages'] = array_filter($product_data['storages'], function($v) { 
            return (array_filter($v));
        });

        array_walk($product_data['storages'], function(&$storage, $id) use ($product_id) {
            $storage['storage_id'] = $storage['storage_id'] ?? $id;
            $storage['product_id'] = $product_id;
        });

        if (!empty($product_data['storages'])) db_query('INSERT INTO ?:storages_products ?m', $product_data['storages']);
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
        
        // мы не ставим юзергруппы к складу совсем так как склад не определяет цену товара так как цену товара определяет прайсовая юзергруппа.
        if (!empty($storage['usergroup_ids'])) {
            // но в перспективе может быть и таки да.
            $usergroup_ids = array_intersect($usergroup_ids, $storage['usergroup_ids']);
        }

        $field_list .= db_quote(', ?:storages_products.amount, ?:storages_products.min_qty as storage_min_qty, ?:storages_products.qty_step as storage_qty_step');

        // если товара на складе быть не может, не достаем его = right join 
        $join .= db_quote(' RIGHT JOIN ?:storages_products ON ?:storages_products.product_id = ?i AND ?:storages_products.storage_id = ?i', $product_id, $storage['storage_id']);

        // если у товара нет прайсов, то тоже не достаем его
        $price_usergroup = db_quote('AND ?:product_prices.usergroup_id IN (?a)', array_diff($usergroup_ids, [USERGROUP_ALL]));
        $condition .= db_quote("AND ?:product_prices.price IS NOT NULL");

        // заменим условие наличия товара
        // TODO штатно поля show_out_of_stock_product нет!
        $old_condition = db_quote(
            ' AND (CASE ?:products.show_out_of_stock_product' .
            "   WHEN ?s THEN (?:products.amount > 0 OR ?:products.tracking = 'D')" .
            '   ELSE 1' .
            ' END)',
            'N'
        );
        $new_condition = db_quote(
            ' AND (CASE ?:products.show_out_of_stock_product' .
            "   WHEN ?s THEN (?:storages_products.amount > 0 OR ?:products.tracking = 'D')" .
            '   ELSE 1' .
            ' END)',
            'N'
        );
        $condition = str_replace($old_condition, $new_condition, $condition );
    }
}

function fn_storages_get_product_data_post(&$product_data) {
    if ($storage = Registry::get('runtime.current_storage')) {
        $product_data['min_qty'] = !empty($product_data['storage_min_qty']) ? $product_data['storage_min_qty'] : $product_data['min_qty'];
        $product_data['qty_step'] = !empty($product_data['storage_qty_step']) ? $product_data['storage_qty_step'] : $product_data['qty_step'];
        unset($product_data['storage_min_qty'], $product_data['storage_qty_step']);
    }
}

function fn_storages_load_products_extra_data(&$extra_fields, $products, $product_ids, &$params, $lang_code) {
    if ($storage = Registry::get('runtime.current_storage')) {
        $extra_fields['?:storages_products'] = [
            'primary_key' => 'product_id',
            'fields' => [
                'amount', 'min_qty as storage_min_qty', 'qty_step as storage_qty_step'
            ],
            'condition' => db_quote(' AND ?:storages_products.storage_id = ?i', $storage['storage_id'])
        ];

        if (isset($storage['usergroup_ids']) && !empty(array_filter($storage['usergroup_ids']))) {
            $params['auth_usergroup_ids'] = array_intersect($params['auth_usergroup_ids'], $storage['usergroup_ids']);
        }
    }
}

function fn_storages_load_products_extra_data_post(&$products, $product_ids, $params, $lang_code) {
    foreach ($products as &$product) {
        fn_storages_get_product_data_post($product);
    }
}

function fn_storages_get_products(array &$params, array &$fields, array &$sortings, &$condition, &$join, $sorting, $group_by, $lang_code, $having)
{
    if ($storage = Registry::get('runtime.current_storage')) {
        // нам нужно исключить из выборки товары не отгружаемые с текущего склада
        $join .= db_quote(
           ' RIGHT JOIN ?:storages_products AS sp'
           . ' ON sp.product_id = products.product_id AND sp.storage_id = ?i ', $storage['storage_id']);

        // нам нужно исключить из выборки товары без прайсов
        $auth = Tygh::$app['session']['auth'];

        // и тут же отработать пользовательские цены
        $join .= db_quote(' LEFT JOIN ?:user_price AS up ON up.product_id = products.product_id AND up.user_id = ?i AND up.storage_id = ?i', $auth['user_id'], $storage['storage_id']);

        $old_price_condition = db_quote(' AND prices.usergroup_id IN (?n)', (($params['area'] == 'A') ? USERGROUP_ALL : array_merge(array(USERGROUP_ALL), $auth['usergroup_ids'])));
        $price_condition = db_quote(' AND ((prices.usergroup_id IN (?n) AND prices.price IS NOT NULL) OR up.price IS NOT NULL)', (($params['area'] == 'A') ? USERGROUP_ALL : array_filter($auth['usergroup_ids'])));
        $condition = str_replace($old_price_condition, $price_condition, $condition);

        // заменим условие наличия товара
        // TODO штатно поля show_out_of_stock_product нет!
        $old_condition = db_quote(
            ' AND (CASE products.show_out_of_stock_product' .
            "   WHEN ?s THEN (products.amount > 0 OR products.tracking = 'D')" .
            '   ELSE 1' .
            ' END)',
            'N'
        );
        $new_condition = db_quote(
            ' AND (CASE products.show_out_of_stock_product' .
            "   WHEN ?s THEN (sp.amount > 0 OR products.tracking = 'D')" .
            '   ELSE 1' .
            ' END)',
            'N'
        );
        $condition = str_replace($old_condition, $new_condition, $condition );
    } elseif (!empty(Registry::get('runtime.storages'))) {
        // if we have not selected storage we cannot buy
        $condition .= db_quote('AND 0');
    }
}

function fn_init_storages() {
    if (AREA != 'C') {
        return array(INIT_STATUS_OK);
    }

    // $storages = Registry::getOrSetCache(
    //     'fn_get_storages',
    //     ['storages', 'storage_usergroups'],
    //     'user',
    //     static function () {
    //         list($storages) = fn_get_storages(['get_usergroups' => true]);
    //         return $storages;
    //     }
    // );

    list($storages) = fn_get_storages(['get_usergroups' => true]);

    if (!empty($storages)) {
        if (!empty($_REQUEST['storage']) && !empty($storages[$_REQUEST['storage']])) {
            $storage = $_REQUEST['storage'];
        } elseif (!empty($_REQUEST['storage_id']) && !empty($storages[$_REQUEST['storage_id']])) {
            $storage = $_REQUEST['storage_id'];
        } elseif (($s = fn_get_session_data('storage')) && !empty($storages[$s])) {
            $storage = $s;
        } else {
            Registry::set('runtime.force_to_choose_storage', true);
        }

        Registry::set('runtime.storages', $storages);
        if (!empty($storage)) {
            if ($storage != fn_get_session_data('storage')) {
                fn_set_session_data('storage', $storage, COOKIE_ALIVE_TIME);
            }

            Registry::set('runtime.current_storage', $storages[$storage]);
            fn_define('STORAGE', $storage);
        } else {
            Registry::set('runtime.current_storage', false);
            fn_define('STORAGE', false);
        }

        Tygh::$app['view']->assign('storages', $storages);
    }

    return array(INIT_STATUS_OK);
}

function fn_storages_login_user_post($user_id, $cu_id, $udata, $auth, $condition, $result) {
    fn_delete_session_data('storage');
}

function fn_storages_user_logout_before_save_cart($auth) {
    fn_delete_session_data('storage');
}

function fn_storages_pre_add_to_cart(&$product_data, $cart, $auth, $update) {
    if ($update) {
        foreach ($product_data as $key => &$data) {
            $data['extra']['storage_id'] = isset($cart['products'][$key]['extra']['storage_id']) ? $cart['products'][$key]['extra']['storage_id'] : Registry::ifGet('runtime.current_storage.storage_id', false);
        }
        unset($data);
    } elseif ($storage_id = Registry::ifGet('runtime.current_storage.storage_id', false)) {
        foreach ($product_data as $key => &$data) {
            if (empty($data['extra']['storage_id'])) $data['extra']['storage_id'] = $storage_id;
        }
        unset($data);
    }
}

function fn_storages_add_product_to_cart_get_price($product_data, $cart, $auth, $update, $_id, $data, $product_id, $amount, &$price, $zero_price_action, $allow_add) {
    // if we have the storages for current user

    if ($storages = Registry::get('runtime.storages')) {
        $usergroup_ids = $auth['usergroup_ids'];

        $storage = $storages[$data['extra']['storage_id']];
        if (!empty($storage['usergroup_ids'])) {
            $usergroup_ids = array_intersect($usergroup_ids, $storage['usergroup_ids']);
        }
        $usergroup_ids = array_filter($usergroup_ids);

        if ($usergroup_ids) {
            $price = db_get_field("SELECT IF(prices.percentage_discount = 0, prices.price, prices.price - (prices.price * prices.percentage_discount)/100) as price FROM ?:product_prices prices WHERE product_id = ?i AND lower_limit = ?i AND usergroup_id IN (?a)", $product_id, 1, $usergroup_ids);
        }
    }
}

function fn_storages_pre_get_cart_product_data($hash, $product, $skip_promotion, $cart, $auth, $promotion_amount, &$fields, &$join, $params) {
    if ($storages = Registry::get('runtime.storages') && !empty($product['extra']['storage_id']) && !isset($product['extra']['exclude_from_calculate'])) {
        $fields[] = '?:storages_products.qty_step as storage_qty_step';
        $fields[] = '?:storages_products.min_qty storage_min_qty';
        $join .= db_quote(' RIGHT JOIN ?:storages_products ON ?:storages_products.product_id = ?:products.product_id AND ?:storages_products.storage_id = ?i', $product['extra']['storage_id']);
    }
}

function fn_storages_get_cart_product_data($product_id, &$_pdata, $product, $auth, $cart, $hash) {
    if ($storages = Registry::get('runtime.storages')) {
        $usergroup_ids = $auth['usergroup_ids'];
        $_pdata['min_qty'] = !empty($_pdata['storage_min_qty']) ? $_pdata['storage_min_qty'] : $_pdata['min_qty'];
        $_pdata['qty_step'] = !empty($_pdata['storage_qty_step']) ? $_pdata['storage_qty_step'] : $_pdata['qty_step'];

        $storage = $storages[$product['extra']['storage_id']];
        if (!empty($storage['usergroup_ids'])) {
            $usergroup_ids = array_intersect($usergroup_ids, $storage['usergroup_ids']);

            $usergroup_ids = array_filter($usergroup_ids);
            if ($usergroup_ids) {
                $_pdata['price'] = db_get_field("SELECT min(IF(prices.percentage_discount = 0, prices.price, prices.price - (prices.price * prices.percentage_discount)/100)) as price FROM ?:product_prices prices WHERE product_id = ?i AND lower_limit = ?i AND usergroup_id IN (?a)", $product_id, 1, $usergroup_ids);
            }

            fn_set_hook('storages_get_cart_product_data', $product_id, $_pdata, $product, $auth, $cart, $hash);
        }

        // исключить товар без цены
        if ($_pdata['price'] === '') {
            unset($_pdata['product_id']);
        }

        $_pdata['storage_id'] = $product['extra']['storage_id'];
    }
}

function fn_storages_generate_cart_id(&$_cid, $extra) {
    if (!empty($extra['storage_id'])) {
        $_cid['storage_id'] = $extra['storage_id'];
    }
}

function fn_storages_check_amount_in_stock_before_check($product_id, $amount, $product_options, $cart_id, $is_edp, $original_amount, $cart, $update_id, &$product, &$current_amount) {

    $storage_id = $cart['products'][$cart_id]['extra']['storage_id'] ?? Registry::ifGet('runtime.current_storage.storage_id', false);

    if ($storage_id) {
        $product = db_get_row(
            'SELECT p.tracking, s.amount, p.min_qty, p.max_qty, p.qty_step, s.min_qty AS storage_min_qty, s.qty_step AS storage_qty_step, p.list_qty_count, p.out_of_stock_actions, p.product_type, pd.product'
            . ' FROM ?:products AS p'
            . ' LEFT JOIN ?:product_descriptions AS pd ON pd.product_id = p.product_id AND lang_code = ?s'
            . ' RIGHT JOIN ?:storages_products AS s ON s.product_id = p.product_id AND storage_id = ?i'
            . ' WHERE p.product_id = ?i',
            CART_LANGUAGE,
            $storage_id,
            $product_id
        );

        $product['min_qty'] = !empty($product['storage_min_qty']) ? $product['storage_min_qty'] : $product['min_qty'];
        $product['qty_step'] = !empty($product['storage_qty_step']) ? $product['storage_qty_step'] : $product['qty_step'];

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

function fn_storages_calculate_cart_content_before_shipping_calculation($cart, $auth, &$calculate_shipping, $calculate_taxes, $options_style, $apply_cart_promotions, $shipping_cache_tables, $shipping_cache_key) {
    if ($storages = Registry::get('runtime.storages') && !empty($cart['product_groups'])) {
        foreach ($cart['product_groups'] as $group) {
            if (empty($group['storage_id'])) {
                $calculate_shipping = 'A';
                break;
            }
        }
    }
}

function fn_storages_shippings_group_products_list(&$products, &$groups) {
    if ($storages = Registry::get('runtime.storages')) {

        $storages_groups = array();
        foreach ($groups as $group) {
            foreach ($group['products'] as $cart_id => $product) {
                // extra????? $product['extra']['storage_id']
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
    } else {
        $cart['storage_id'] = 0;
    }
}

function fn_storages_update_product_amount_pre($product_id, $amount_delta, $product_options, $sign, $tracking, &$current_amount, $product_code, $notify, $order_info, $cart_id) {
    if ($order_info['storage_id']) {
        $current_amount = db_get_field('SELECT amount FROM ?:storages_products WHERE storage_id = ?i AND product_id = ?i', $order_info['storage_id'], $product_id);
    }
}

function fn_storages_update_product_amount($new_amount, $product_id, $cart_id, $tracking, $notify, $order_info, $amount_delta, $current_amount, $original_amount, $sign) {
    if ($order_info['storage_id']) {
        if (in_array($product_id, ['157509', '157542', '157541', '157543'])) {
            return;
        }
        db_query('UPDATE ?:storages_products SET amount = ?d WHERE product_id = ?i AND storage_id = ?i', $new_amount, $product_id, $order_info['storage_id']);
    }
}

function fn_storages_get_orders($params, $fields, $sortings, &$condition, $join, $group) {
    if (!empty($params['storage_id'])) {
        $condition .= db_quote(' AND ?:orders.storage_id = ?i', $params['storage_id']);
    }
}

function fn_storages_get_order_info(&$order, $additional_data) {
    if (!empty($order['storage_id'])) {
        list($storages,) = fn_get_storages(['storage_id' => $order['storage_id']]);
        if (!empty($storages)) $order['storage'] = reset($storages);
    }
}

function fn_storages_calculate_cart_items(&$cart, &$cart_products, $auth, $apply_cart_promotions) {
    if ($storages = Registry::get('runtime.storages')) {
        foreach ($cart['products'] as $cart_id => $product) {
            if (!(isset($product['extra']['storage_id']) && in_array($product['extra']['storage_id'], array_column($storages, 'storage_id')))) {
                fn_delete_cart_product($cart, $cart_id);
                unset($cart_products[$cart_id]);
            }
        }
    }
}

function fn_storages_reorder_product($order_info, &$cart, $auth, $product, $amount, $price, $zero_price_action, $k) {
    if ($storages = Registry::get('runtime.storages')) {
        if (empty($product['extra']['storage_id']) || !in_array($product['extra']['storage_id'], array_keys($storages))) $cart['products'][$k]['extra']['storage_id'] = Registry::get('runtime.current_storage.storage_id');
    }
}

function fn_storages_monolith_generate_xml($order_info, $monolith_order, &$d_record) {
    if (!empty($order_info['storage'])) {
        $d_record[3] = $order_info['storage']['code'];
    }
}

function fn_storages_get_user_price($params, $join, &$condition) {
    if (isset($params['product']['extra']['storage_id'])) {
        $storage_id = $params['product']['extra']['storage_id'];
    } elseif ($storage = Registry::get('runtime.current_storage')) {
        $storage_id = $storage['storage_id'];
    }
    if (!empty($storage_id)) {
        $condition .= db_quote(' AND (p.storage_id = ?i OR p.storage_id = 0)', $storage_id);
    }
}

function fn_storages_user_price_exim_product_price_pre($object, &$price) {
    static $db_storages;
    if (empty($object['storage_id'])) {
        $storage_id = 0;  
    } else {
        if (!isset($db_storages[$object['storage_id']])) $db_storages[$object['storage_id']] = db_get_field('SELECT storage_id FROM ?:storages WHERE code = ?s', $object['storage_id']);
        $storage_id = (!empty($db_storages[$object['storage_id']])) ? $db_storages[$object['storage_id']] : 0;
    }

    foreach ($price as &$row) {
        $row['storage_id'] = $storage_id;
    }
    unset($row);
}

function fn_storages_delete_usergroups($usergroup_ids) {
    db_query('DELETE FROM ?:storage_usergroups WHERE usergroup_id IN (?a)', $usergroup_ids);
}

function fn_storages_api_runtime_handle_index_request($id, $params, $status, &$data) {
    if ($id == 'storage') $data['storage'] = Registry::get('runtime.current_storage');
    if ($id == 'storage_id') $data['storage'] = Registry::get('runtime.current_storage');
}

function fn_storages_api_runtime_handle_create_request($params, $status, &$data) {
    if (isset($params['storage'])) $data['storage'] = Registry::get('runtime.current_storage');
    if (isset($params['storage_id'])) {
        // support raw json post API request also form-data
        $_REQUEST['storage_id'] = $params['storage_id'];
        fn_init_storages();
        $data['storage'] = Registry::get('runtime.current_storage');
    }
}

function fn_storages_api_runtime_handle_delete_request($id, $status, $data) {
    if ($id == 'storage') fn_delete_session_data('storage');
}
