<?php

use Tygh\Registry;
use Tygh\Enum\YesNo;
use Tygh\Enum\SiteArea;
use Tygh\Enum\NotificationSeverity;
use Tygh\Enum\RewardPointsMechanics;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_extended_reward_points_update_product_post($product_data, $product_id) {
    if (isset($product_data['reward_points']) && YesNo::toBool($product_data['is_op'])) {
        $usergroup_ids = array_column($product_data['reward_points'], 'usergroup_id');
        if (!empty($usergroup_ids)) {
            db_query('DELETE FROM ?:reward_points WHERE object_id = ?i AND object_type = ?s AND usergroup_id NOT IN (?a)', $product_id, PRODUCT_REWARD_POINTS, $usergroup_ids);
        }
    }
}

function fn_extended_reward_points_gather_additional_product_data_before_discounts(&$product, $auth, $params) {
    if (SiteArea::isStorefront(AREA) && YesNo::toBool($product['is_pbp']) && RewardPointsMechanics::isFullPayment($auth['extended_reward_points']['reward_points_mechanics'])) {
        $product['point_price'] = fn_extended_reward_points_get_price_in_points($product, $auth);
    }
}

// temporary fix for mobile app
function fn_extended_reward_points_storefront_rest_api_gather_additional_products_data_post(&$products, $params, $data_gather_params) {
    $auth = Tygh::$app['session']['auth'];
    foreach ($products as &$product) {
        if (SiteArea::isStorefront(AREA) && YesNo::toBool($product['is_pbp']) && RewardPointsMechanics::isFullPayment($auth['extended_reward_points']['reward_points_mechanics'])) {
            $product['price'] = 0;
        }
    }

    unset($product);
}

function fn_extended_reward_points_pre_get_cart_product_data($hash, $product, $skip_promotion, $cart, $auth, $promotion_amount, &$fields, &$join, $params) {
    $fields[] = '?:products.is_pbp';
    $fields[] = '?:products.is_oper';
    $fields[] = '?:products.is_op';
}

function fn_extended_reward_points_get_cart_product_data($product_id, &$_pdata, &$product, $auth, $cart, $hash) {
    $product['is_pbp'] = $_pdata['is_pbp'];
    $product['is_oper'] = $_pdata['is_oper'];
}

function fn_extended_reward_points_calculate_cart_taxes_pre(&$cart, &$cart_products, $shipping_rates, $calculate_taxes, $auth) {
    if (!empty($auth['extended_reward_points'])) {
        if (RewardPointsMechanics::isPartialPayment($auth['extended_reward_points']['reward_points_mechanics'])) {
            $min_original_subtotal = $cart['original_subtotal'];
            $min_subtotal = $max_rp = 0;

            foreach ($cart['products'] as &$product) {
                if (!YesNo::toBool($product['is_pbp'])) continue;
                $min_subtotal += $product['price'] * $product['amount'] * (1 - ($auth['extended_reward_points']['max_product_discount'] / 100));
                $max_rp += round($product['price'] * $product['amount'] * $auth['extended_reward_points']['max_rp_discount'] / 100);
                unset($product['extra']['points_info']['price']);
            }
            unset($product);

            $max_products_discount = round($cart['subtotal'] - $min_subtotal);

            $cart['points_info']['max_reward_points'] = min($max_rp, $max_products_discount);
            if ($cart['points_info']['in_use']['points'] > $cart['points_info']['max_reward_points']) {
                $cart['points_info']['in_use']['points'] = $cart['points_info']['max_reward_points'];
                fn_set_notification(NotificationSeverity::NOTICE, __('notice'), __('extended_reward_points.points_amount_reduced'));
            }
        } else {
            foreach ($cart['products'] as $key => &$data) {
                // temporary fix for mobile app
                $data['extra']['pay_by_points']['point_price'] = $data['extra']['point_price'] ?? 0;

                if (!YesNo::toBool($data['is_pbp'])) continue;
                $data['extra']['point_price'] = fn_extended_reward_points_get_price_in_points($data, $auth);
                $cart_products[$key]['price'] = 0;
            }
            unset($data);

            $cart['extended_points_info']['in_use'] = fn_get_cart_points_in_use($cart);
        }
    }
}

function fn_extended_reward_points_pre_place_order($cart, &$allow, $product_groups) {
    if (fn_get_cart_points_in_use($cart)) {
        $balance = Tygh::$app['session']['auth']['points'] - fn_get_cart_points_in_use($cart);

        if ($balance < 0) {
            $allow = false;
            fn_set_notification(NotificationSeverity::WARNING, __('WARNING'), __('extended_reward_points.not_enough_points', [abs($balance)]));
        }
    }
}

function fn_extended_reward_points_get_order_info(&$order_info, $additional_data) {
    if (!empty($order_info) && $points = fn_get_cart_points_in_use($order_info)) {
        $order_info['points_info']['in_use']['points'] = $points;
    }
}

function fn_extended_reward_points_user_init(&$auth, $user_info) {
    if (empty($auth['extended_reward_points'])) {
        if (!empty($user_info['extended_reward_points'])) {
            $auth['extended_reward_points'] = $user_info['extended_reward_points'];
        } else {
            $auth['extended_reward_points'] = db_get_row('SELECT reward_points_mechanics, max_rp_discount, max_product_discount FROM ?:companies WHERE company_id = ?i', $auth['company_id']);
        }
    }
}

function fn_extended_reward_points_fill_auth(&$auth, $user_data, $area, $original_auth) {
    if (empty($auth['extended_reward_points']) && !empty(Tygh::$app['session']['auth']['extended_reward_points']) && Tygh::$app['session']['auth']['user_id'] == $auth['user_id'] && defined('API') && SiteArea::isStorefront(AREA)) {
        $auth['extended_reward_points'] = Tygh::$app['session']['auth']['extended_reward_points'];
        $auth['points'] = Tygh::$app['session']['auth']['points'];
    }
}

// vega
function fn_extended_reward_points_add_product_to_cart_get_price($product_data, $cart, $auth, $update, $_id, &$data, $product_id, $amount, $price, $zero_price_action, &$allow_add) {

    if (RewardPointsMechanics::isFullPayment($auth['extended_reward_points']['reward_points_mechanics']) && $allow_add) {
        if (!empty($cart['products'][$_id])) {
            $data['is_pbp'] = $cart['products'][$_id]['is_pbp'];
            $data['extra']['point_price'] = $cart['products'][$_id]['extra']['point_price'];
        } else {
            $data = fn_array_merge($data, db_get_row('SELECT is_pbp, is_op, is_oper FROM ?:products WHERE product_id = ?i', $product_id));
        }

        if (YesNo::toBool($data['is_pbp'])) {
            $data['extra']['is_pbp'] = $data['is_pbp'];
            if (!empty($cart['products'][$_id])) {
                $data['extra']['point_price'] = $cart['products'][$_id]['extra']['point_price'];
            } else {
                $tmp = $data;
                $tmp['price'] = $price;
                $data['extra']['point_price'] = fn_extended_reward_points_get_price_in_points($tmp, $auth);
            }
            
            $in_use = fn_get_cart_points_in_use($cart, ($update) ? $product_id : false);
            
            $balance = $auth['points'] - $data['extra']['point_price'] * $amount - $in_use;
            if ($balance < 0) {
                fn_set_notification(NotificationSeverity::WARNING, __('WARNING'), __('extended_reward_points.not_enough_points', [abs($balance)]));
                $allow_add = false;
            }
        }
    }
}

function fn_extended_reward_points_get_price_in_points($product, $auth) {
    if (YesNo::toBool(Registry::get('addons.reward_points.auto_price_in_points')) && !YesNo::toBool($product['is_oper'])) {
        $per = Registry::get('addons.reward_points.point_rate');
        $subtotal = $product['price'];
        if (!YesNo::toBool(Registry::get('addons.reward_points.price_in_points_with_discounts')) && isset($product['discount'])) {
            $subtotal = $product['price'] + $product['discount'];
        }
    } else {
        $subtotal = fn_get_price_in_points($product['product_id'], $auth);
    }

    return ceil($subtotal);
}

function fn_get_cart_points_in_use($cart, $exclude_products = []) {
    if (empty($cart)) $cart = Tygh::$app['session']['cart'];

    if (!is_array($exclude_products)) {
        $exclude_products = [$exclude_products];
    }

    $total_use_points = 0;
    if (!empty($cart['products']))
    foreach ($cart['products'] as &$product) {
        if (in_array($product['product_id'], $exclude_products) || !YesNo::toBool($product['extra']['is_pbp'])) continue;

        $total_use_points += $product['extra']['point_price'] * $product['amount'];
    }

    return $total_use_points;
}

function fn_extended_reward_points_change_order_status(&$status_to, &$status_from, &$order_info, &$force_notification, &$order_statuses, &$place_order) {
    if ($reward_points_ttl = db_get_field('SELECT reward_points_ttl FROM ?:companies WHERE company_id = ?i', $order_info['company_id'])) {

        $points_info = (isset($order_info['points_info'])) ? $order_info['points_info'] : array();

        if (!empty($points_info)) {
            $grant_points_to = (!empty($order_statuses[$status_to]['params']['grant_reward_points'])) ? $order_statuses[$status_to]['params']['grant_reward_points'] : 'N';
            $grant_points_from = (!empty($order_statuses[$status_from]['params']['grant_reward_points'])) ? $order_statuses[$status_from]['params']['grant_reward_points'] : 'N';

            if ($order_statuses[$status_to]['params']['inventory'] == 'I' && $order_statuses[$status_from]['params']['inventory'] == 'D') {
                if (!empty($points_info['in_use']['points'])) {
                    fn_extended_reward_points_release_points($order_info['order_id']);
                }
            }
            if ($order_statuses[$status_to]['params']['inventory'] == 'D' && $order_statuses[$status_from]['params']['inventory'] == 'I') {
                if (!empty($points_info['in_use']['points'])) {
                    // decrease points in use
                    if ($points_info['in_use']['points'] <= fn_get_user_additional_data(POINTS, $order_info['user_id'])) {
                        fn_extended_reward_points_use_points($points_info['in_use']['points'], $order_info['user_id'], $order_info['order_id']);
                    }
                }
            }

            if (
                $grant_points_to === YesNo::YES && $points_info['is_gain'] === YesNo::NO && !empty($points_info['reward'])
            ) {
                if (!db_get_field('SELECT order_id FROM ?:reward_point_details WHERE order_id = ?i', $order_info['order_id'])) {
                    $insert = [
                        'user_id' => $order_info['user_id'],
                        'order_id' => $order_info['order_id'],
                        'amount' => $points_info['reward'],
                        'ttl' => time() + $reward_points_ttl * SECONDS_IN_DAY,
                        'repaid_order_ids' => '',
                        'details' => serialize(''),
                    ];
                    db_query('INSERT INTO ?:reward_point_details SET ?u', $insert);
                }
            }
        }
    }
}

function fn_extended_reward_points_expire_points() {
    if ($data = db_get_array('SELECT * FROM ?:reward_point_details WHERE ttl < ?i AND amount > ?s', TIME, 0)) {
        foreach ($data as $expiry) {
            $reason = array(
                'order_id' => $expiry['order_id'],
                'text' => __('extended_reward_points.expired_reward_points_ttl'),
            );
            fn_change_user_points(-$expiry['amount'], $expiry['user_id'], serialize($reason), CHANGE_DUE_SUBTRACT);
        }
        db_query('UPDATE ?:reward_point_details SET amount = ?i WHERE ttl < ?i', 0, TIME);
    }
}

function fn_extended_reward_points_use_points($points, $user_id, $repaid_order_id) {
    if (empty($user_id)) return;

    if ($data = db_get_row('SELECT * FROM ?:reward_point_details WHERE user_id = ?i AND amount != ?i AND order_id != ?i ORDER BY ttl', $user_id, 0, $repaid_order_id)) {
        $data['details'] = unserialize($data['details']);
        $data['repaid_order_ids'] = array_filter(explode(',', $data['repaid_order_ids']));

        $diff = $data['amount'] - $points;
        $decrease = $data['amount'] - max(0, $diff);
        $data['details'][$repaid_order_id] = ['time' => TIME, 'decrease' => $decrease];
        $data['repaid_order_ids'][] = $repaid_order_id;
        $data['repaid_order_ids'] = array_unique($data['repaid_order_ids']);
        $data['amount'] = max(0, $diff);

        $data['details'] = serialize($data['details']);
        $data['repaid_order_ids'] = implode(',', $data['repaid_order_ids']);

        db_query('UPDATE ?:reward_point_details SET ?u WHERE order_id = ?i', $data, $data['order_id']);
        if ($diff < 0) {
            fn_extended_reward_points_use_points(abs($diff), $user_id, $repaid_order_id);
        }
    }
}

function fn_extended_reward_points_release_points($repaid_order_id) {
    if ($items = db_get_array('SELECT * FROM ?:reward_point_details WHERE FIND_IN_SET(?i, repaid_order_ids)', $repaid_order_id)) {
        foreach($items as $data) {
            $data['details'] = unserialize($data['details']);
            $data['repaid_order_ids'] = array_filter(explode(',', $data['repaid_order_ids']));
            if (!empty($data['details'][$repaid_order_id])) {
                $data['amount'] += $data['details'][$repaid_order_id]['decrease'];
                unset($data['details'][$repaid_order_id]);
                if (($key = array_search($repaid_order_id, $data['repaid_order_ids'])) !== false) {
                    unset($data['repaid_order_ids'][$key]);
                }
            }

            $data['details'] = serialize($data['details']);
            $data['repaid_order_ids'] = implode(',', $data['repaid_order_ids']);
            db_query('UPDATE ?:reward_point_details SET ?u WHERE order_id = ?i', $data, $data['order_id']);
        }
    }
}

function fn_generate_reward_points_report($params) {
    $default_params = array(
        'delimiter' => 'S',
        'company_id' => '45',
        'period' => 'custom',
        'time_from' => strtotime("-3 month"),
        'time_to' => TIME,
        'filename' => date('dMY_His', TIME) . '.csv',
    );

    $params = array_filter($params);
    if (isset($params['period']) && $params['period'] == 'A') unset($params['period']);

    if (is_array($params)) {
        $params = array_merge($default_params, $params);
    } else {
        $params = $default_params;
    }

    list($params['time_from'], $params['time_to']) = fn_create_periods($params);

    if (Registry::get('runtime.company_id')) {
        $params['company_id'] = Registry::get('runtime.company_id');
    }

    $output = array();
    if (isset($params['is_search'])) {
        list($orders, $c_params, $totals) = fn_get_orders($params);

        $ttl = db_get_hash_single_array('SELECT ttl, order_id FROM ?:reward_point_details WHERE order_id IN (?a)', array('order_id', 'ttl'), array_column($orders, 'order_id'));
        $points_info = db_get_hash_single_array('SELECT data, order_id FROM ?:order_data WHERE order_id IN (?a) AND type = ?s', array('order_id', 'data'), array_column($orders, 'order_id'), POINTS_IN_USE);
        $users_data = db_get_hash_array('SELECT u.user_id, u.user_login, p.s_state, d.data FROM ?:users AS u LEFT JOIN ?:user_profiles AS p ON u.user_id = p.user_id LEFT JOIN ?:user_data AS d ON d.user_id = u.user_id AND d.type = ?s WHERE u.user_id IN (?a)', 'user_id', POINTS, array_column($orders, 'user_id'));

        if ($orders) {
            foreach ($orders as $order) {
                $output[] = [
                    __('region') => $users_data[$order['user_id']]['s_state'],
                    __('login') => $users_data[$order['user_id']]['user_login'],
                    __('order') => $order['order_id'],
                    __('order_date') => fn_date_format($order['timestamp'], Registry::get('settings.Appearance.date_format')),
                    __('earned_points') => $order['points'] ?? 0,
                    __('extended_reward_points.reward_points_ttl') => (!empty($ttl[$order['order_id']])) ? fn_date_format($ttl[$order['order_id']], Registry::get('settings.Appearance.date_format')) : '-',
                    __('points_in_use') => (!empty($points_info[$order['order_id']])) ? unserialize($points_info[$order['order_id']])['points'] : 0,
                    __('extended_reward_points.user_points') => (!empty($users_data[$order['user_id']]['data'])) ? unserialize($users_data[$order['user_id']]['data']) : 0
                ];
            }
        }
    }
    return array($output, $params);
}

function fn_extended_reward_points_get_autostickers_pre(&$stickers, $product, $auth, $params) {
    if (empty($params['get_for_one_product'])) {
        // probably heavy
        fn_gather_reward_points_data($product, $auth);
    }

    if (!empty($product['points_info']['reward']['amount'])) $stickers['grant_rp'] = Registry::get('addons.extended_reward_points.grant_rp_sticker_id');
    if (!empty($product['points_info']['price'])) $stickers['reduce_rp'] = Registry::get('addons.extended_reward_points.reduce_rp_sticker_id');
}
