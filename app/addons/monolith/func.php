<?php

use Tygh\Enum\SiteArea;
use Tygh\Registry;
use Tygh\Http;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_monolith_generate_xml($order_id) {
    $schema = fn_get_schema('monolith', 'schema');
    $addon = Registry::get('addons.monolith');
    $allowed_companies = explode(',', $addon['company_ids']);

    $order_info = fn_get_order_info($order_id);

    if (!in_array($order_info['company_id'], $allowed_companies)) {
        return false;
    }
    $company_settings = db_get_row('SELECT reward_points_mechanics, max_rp_discount, max_product_discount FROM ?:companies WHERE company_id = ?i', $order_info['company_id']);
    if (isset($order_info['points_info']['in_use']['points'])) {
        $d_products = [];
        foreach ($order_info['product_groups'] as $group_id => $group) {
            foreach ($group['products'] as $product) {
                $available_discount = $product['price']*(1 - $company_settings['max_product_discount']/100);
                $d_products[$product['item_id']] = ($available_discount > 0) ? $available_discount * $product['amount'] : 0;
            }
        }
        arsort($d_products);

        $coeff = $order_info['points_info']['in_use']['points'] / array_sum($d_products);
        foreach ($order_info['products'] as &$product) {
            $product['points_in_use'] = round($d_products[$product['item_id']] * $coeff);
        }
        unset($product);

        $spread_points = array_sum(array_column($order_info['products'], 'points_in_use'));
        if ($rounding_error = $order_info['points_info']['in_use']['points'] - $spread_points) {
            $order_info['products'][key($d_products)]['points_in_use'] += $rounding_error;
        }
    }

    $monolith_order = &$schema['extdata']['scheme']['data']['o'];
    $monolith_order['d'][] = array(
        '@attributes' => array('name' => 'CRMOrderParam'),
        'r' => array(
            'f' => array(
                date("Y-m-d\T00:00:00", $order_info['timestamp']),
                1,
                1,
                date("Y-m-d\T00:00:00", $order_info['delivery_date']),
            ),
        ),
    );
    $user_code = !empty($order_info['fields'][39]) ? $order_info['fields'][39] : $order_info['fields'][38];
    $d_record = [
        $addon['order_prefix'] . $order_id,
        date("Y-m-d\T00:00:00", $order_info['timestamp']),
        //'', //CompanyId
        $user_code, //CRMClientId
        '123',
        $order_info['user_id'],
        date("Y-m-d\T00:00:00", $order_info['delivery_date']),
        ($order_info['group_id'] == '19') ? 'CustReturn' : 'CustOrder',
        'Entered',
    ];

    fn_set_hook('monolith_generate_xml', $order_info, $monolith_order, $d_record);

    $monolith_order['d'][] = array(
        '@attributes' => array ('name' => 'CRMOrder'),
        'r' => array(
            'f' => $d_record
        ),
    );

    $iterator = 0;

    $CRMOrderLine = [];

    foreach ($order_info['products'] as $k => $p) {
        if (empty($p['extra']['limit_discount_bonus_by_amount_packages'])) continue;
        $base_product = &$order_info['products'][$p['extra']['limit_discount_bonus_by_amount_packages']];

        $sku_total = ($p['price'] * $p['amount'] + $base_product['price'] * $base_product['amount']);
        $base_product['amount'] += $p['amount'];
        $base_product['price'] = $sku_total / $base_product['amount'];
        unset($order_info['products'][$k]);

        if (empty($base_product['promotions'])) continue;
        
        // recalcutale promotion bonus
        $product_promotions = [];
        foreach($base_product['promotions'] as $promotion_id => $promotion) {
            $product_promotions[$promotion_id] = $promotion['bonuses'][0];
        }
        $product_promotions = fn_sort_array_by_key($product_promotions, 'discount', SORT_DESC);
        $promotion_id = key($product_promotions);

        $base_product['promotions'][$promotion_id]['bonuses'][0] = [
            'discount_bonus' => 'by_fixed',
            'discount_value' => $base_product['base_price'] - $base_product['price'],
            'discount' => $base_product['base_price'] - $base_product['price'],
        ];

        unset($base_product);
    }

    foreach ($order_info['products'] as $p) {
        $price = $p['price'];
        if (!empty($p['promotions'])) {
            $bonuses = [];
            foreach ($p['promotions'] as $promotion) {
                foreach ($promotion['bonuses'] as $bonus) {
                    if (empty($bonus['discount'])) continue;
                    $bonuses[$bonus['discount']*100] = $bonus;
                }
            }
            $bonus = $bonuses[max(array_keys($bonuses))];
            if ($bonus['discount_bonus'] == 'by_percentage') {
                $price = fn_format_price($p['base_price'] * (1-$bonus['discount_value']/100));
            } elseif($bonus['discount_bonus'] == 'to_percentage') {
                $price = fn_format_price($p['base_price'] * ($bonus['discount_value']/100));
            }
        }
        if (isset($p['points_in_use'])) {
            $price -= fn_format_price($p['points_in_use'] / $p['amount']);
        }

        $iterator += 1;
        $CRMOrderLine[] = [
            'f' => array(
                $addon['order_prefix'] . $order_id,
                $iterator,
                // 'smart.wh1',
                // '',
                $p['product_code'],
                'ea',
                $price,
                $p['amount']
            )
        ];
    }

    $monolith_order['d'][] = array(
        '@attributes' => array ('name' => 'CRMOrderLine'),
        'r' => $CRMOrderLine,
    );

    $CRMOrderDiscountLine = [];
    if (!empty($order_info['promotions'])) {
        $promotion_products = array_filter($order_info['products'], function($v) {return (isset($v['promotions']));});
        $applied_promotions = [];
        foreach ($promotion_products as $product) {
            $product_promotions = [];
            foreach($product['promotions'] as $promotion_id => $promotion) {
                $product_promotions[$promotion_id] = $promotion['bonuses'][0];
            }
            $product_promotions = fn_sort_array_by_key($product_promotions, 'discount', SORT_DESC);
            
            foreach($product_promotions as $promotion_id => $value) {
                if (!isset($applied_promotions[$promotion_id])) {
                    $applied_promotions[$promotion_id] = fn_get_promotion_data($promotion_id);
                }

                list($external_id) = explode('.', $applied_promotions[$promotion_id]['external_id']);

                $discount_value = $value['discount'];
                $discount_unit = 'Amount';
                $discount_price = $value['discount_value']; // to_fixed

                if ($value['discount_bonus'] == 'by_fixed') {
                    $discount_price = $product['price'];
                } elseif (in_array($value['discount_bonus'], ['by_percentage', 'to_percentage'])) {
                    $discount_value = $value['discount_value'];
                    $discount_unit = 'Percent';
                    if ($value['discount_bonus'] == 'to_percentage') {
                        $discount_value = 100 - $discount_value;
                    }
                    $discount_price = fn_format_price($product['base_price'] * (1-$discount_value/100));
                }

                $CRMOrderDiscountLine[] = [
                    'f' => array(
                        date("Y-m-d\T00:00:00", $order_info['timestamp']),
                        $addon['order_prefix'] . $order_id,
                        $external_id,
                        htmlspecialchars($applied_promotions[$promotion_id]['name']),
                        fn_format_price($discount_value),
                        $discount_unit,
                        fn_format_price($discount_price),
                        $product['product_code']
                    )
                ];

                break;
            }
        }

        foreach($order_info['promotions'] as $promotion_id => $promotion) {
            if (in_array($promotion_id, array_keys($applied_promotions))) continue;

            $applied_promotions[$promotion_id] = fn_get_promotion_data($promotion_id);
            list($external_id) = explode('.', $applied_promotions[$promotion_id]['external_id']);
            foreach ($applied_promotions[$promotion_id]['bonuses'] as $bonus) {
                if (!in_array($bonus['bonus'], ['promotion_step_free_products', 'free_products'])) continue;
                foreach ($bonus['value'] as $value) {
                    $CRMOrderDiscountLine[] = [
                        'f' => array(
                            date("Y-m-d\T00:00:00", $order_info['timestamp']),
                            $addon['order_prefix'] . $order_id,
                            $external_id,
                            htmlspecialchars($applied_promotions[$promotion_id]['name']),
                            100,
                            'Percent',
                            0,
                            db_get_field('SELECT product_code FROM ?:products WHERE product_id = ?i', $value['product_id']),
                        )
                    ];
                }
            }
        }
    }
    if (isset($order_info['points_info']['in_use']['points'])) {
        $usergroup = db_get_field('SELECT DISTINCT(u.usergroup) FROM ?:usergroup_descriptions AS u LEFT JOIN ?:reward_points AS rp ON u.usergroup_id = rp.usergroup_id LEFT JOIN ?:usergroup_links AS ul ON ul.usergroup_id = u.usergroup_id AND ul.status = ?s WHERE object_type = ?s AND lang_code = ?s AND ul.user_id = ?i AND u.usergroup LIKE ?l', 'A', 'P', DESCR_SL, $order_info['user_id'], '%BaltOn%');

        foreach ($order_info['products'] as $product) {
            if (empty($product['points_in_use'])) continue;
            $CRMOrderDiscountLine[] = [
                'f' => array(
                    date("Y-m-d\T00:00:00", $order_info['timestamp']),
                    $addon['order_prefix'] . $order_id,
                    $usergroup,
                    'Baltika_loyalty',
                    $product['points_in_use'],
                    'Amount',
                    '',
                    $product['product_code'],
                )
            ];
        }
    }

    if (!empty($CRMOrderDiscountLine)) {
        $monolith_order['d'][] = array(
            '@attributes' => array ('name' => 'CRMOrderDiscountLine'),
            'r' => $CRMOrderDiscountLine,
        );
    } else {
        unset($schema['extdata']['scheme']['data']['s']['d'][4]);
    }
    $payment_type_map = [
        '48' => 1,
        '49' => 4,
        '50' => 5,
        '19' => 2
    ];

    $CRMOrderOption = [
        [
            'f' => array(
                $addon['order_prefix'] . $order_id, 
                'PrZakaz', 
                ($order_info['documents_originals']) ? 1 : 0
            )
        ], 
        [
            'f' => array(
                $addon['order_prefix'] . $order_id, 
                'PaymType', 
                $payment_type_map[$order_info['payment_method']['payment_id']]
            )
        ]
    ];

    // if we have comment
    if (!empty($order_info['notes'])) {
        $CRMOrderOption[] = [
            'f' => array(
                $addon['order_prefix'] . $order_id, 
                'Comment', 
                $order_info['notes'],
            )
        ];
    }

    if (!empty($CRMOrderOption)) {
        $monolith_order['d'][] = array(
            '@attributes' => array ('name' => 'CRMOrderOption'),
            'r' => $CRMOrderOption,
        );
    } else {
        unset($schema['extdata']['scheme']['data']['s']['d'][3]);
    }

    return fn_render_xml_from_array($schema);
}

function fn_monolith_send_xml($xml) {
$addon = Registry::get('addons.monolith');
$soap = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:xsd="http://www.w3.org/2001/XMLSchema"
xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
<soap12:Body>
<Request xmlns="http://www.monolit.com/xDataLink/">
<XMLData>
EOT;
    $soap .= fn_html_escape($xml);
    $soap .= <<<EOT
</XMLData>
</Request>
</soap12:Body>
</soap12:Envelope>
EOT;

    $response = HTTP::POST($addon['environment_url'], $soap, array(
        'headers' => array(
            'Content-type: application/soap+xml; charset=utf-8'
        ))
    );

    $result = fn_monolith_parse_soap_response($response);

    return $result;
}

function fn_monolith_parse_soap_response($response) {
    $result = false;

    if (!empty(trim($response))) {
        $data = json_decode(json_encode(simplexml_load_string(str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $response))), 1);

        if (!empty($data)) {
            $xml_response = $data['Body']['RequestResponse']['RequestResult'];
            $result = simplexml_load_string($xml_response);

            return (!empty($result) && !isset($result->error) && !isset($result->scheme->error));
        }
    }

    return false;
}
/*
function fn_monolith_place_order_post($cart, $auth, $action, $issuer_id, $parent_order_id, $order_id, $order_status, $short_order_data, $notification_rules) {
    if (count($cart['product_groups']) == 1) {
        $xml = fn_monolith_generate_xml($order_id);
        if (!empty($xml)) {
            if (fn_monolith_send_xml($xml)) {
                fn_change_order_status($order_id, 'A');
            }
        } 
    }
}*/

// duplicate from frontend controller for mobile application
function fn_monolith_allow_place_order_post(&$cart) {
    if (empty($cart['user_data']['email'])) {
        $cart['user_data']['email'] = fn_checkout_generate_fake_email_address($cart['user_data'], TIME);
    }
}

function fn_monolith_get_promotions_search_by_query($search_fields, &$search_condition, $params) {
    $search_condition[] = db_quote(" (?:promotions.external_id LIKE ?l) ", '%' . $params['name'] . '%');
}

function fn_monolith_get_logos_post($company_id, $layout_id, $style_id, &$logos, $storefront_id) {
    if (Registry::ifGet('runtime.shop_id', 0) == 2 && fn_allowed_for('MULTIVENDOR') && isset($logos['favicon'])) {
        foreach ($logos['favicon']['image'] as &$data) {
            $data = str_replace('favicon', 'baltika_favicon', $data);
        }
    }
}

function fn_monolith_before_dispatch($controller, $mode, $action, $dispatch_extra, $area) {
    if ($controller == 'index' && $mode == 'index' && $_SERVER['REQUEST_METHOD'] == 'GET' && !defined('CONSOLE') && empty($_REQUEST['skey']) && Registry::get('runtime.shop_id') == 2) {
        fn_redirect('categories.view&category_id=9059');
    }
}

function fn_monolith_api_exec($_this, $entity, $entity_properties, $response) {
    if (in_array($entity_properties['name'], ['products', 'users']) && $_this->getRequest()->getMethod() == 'PUT' && count($_this->getRequest()->getData()) == 1) {
        $body = $response->getBody();
        $body[key($_this->getRequest()->getData())] = 'OK';
        $response->setBody($body);
    }
}

function fn_monolith_update_product_post(&$product_data, $product_id, $lang_code, $create) {
    if (isset($product_data['storages']) && defined('API') && !empty($product_id)) {
        $cid = $product_data['company_id'] ?? Registry::get('runtime.company_id') ?? db_get_field("SELECT company_id FROM ?:products WHERE product_id = ?i", $product_id);
        $allowed_cids = explode(',', Registry::get('addons.monolith.company_ids'));
        if (in_array($cid, $allowed_cids)) {
            $current_storages = fn_get_storages_amount($product_id);
            $diff = array_diff_key($current_storages, fn_array_value_to_key($product_data['storages'], 'storage_id'));
            if (!empty($diff)) {
                array_walk($diff, function(&$v) {
                    $v['amount'] = 0;
                    unset($v['product_id']);
                });

                $product_data['storages'] = array_merge($product_data['storages'], array_values($diff));
            }
        }
    }
}

function fn_monolith_get_products($params, $fields, $sortings, &$condition, $join, $sorting, $group_by, $lang_code, $having) {
    if (SiteArea::isStorefront(AREA) && in_array('prices', $params['extend'])) {
        $condition .= db_quote(' AND IF (products.company_id = 45, prices.price IS NOT NULL, 1)');
    }
}

function fn_monolith_get_product_data($product_id, &$field_list, &$join, $auth, $lang_code, &$condition, &$price_usergroup){
    // если у товара балтики нет прайсов, то не достаем его
    if (SiteArea::isStorefront(AREA)) {
        $usergroup_ids = !empty($auth['usergroup_ids']) ? $auth['usergroup_ids'] : array();

        $price_usergroup .= db_quote(' AND IF (?:products.company_id = ?i, ?:product_prices.usergroup_id IN (?a), 1) ', 45, array_filter($usergroup_ids));
        $condition .= db_quote(" AND IF (?:products.company_id = ?i, ?:product_prices.price IS NOT NULL, 1) ", 45);
    }
}

function fn_monolith_create_order($order) {
    if ($order['company_id'] == 45 && $order['storage_id'] == 0) {
        // 1. debug output
        $delivery_date_by_storage = db_get_hash_array('SELECT * FROM ?:user_storages WHERE user_id = ?i ORDER BY storage_id', 'storage_id', $order['user_id']);
        $storage_id = key($delivery_date_by_storage);
        $storages = fn_get_storages(['get_usergroups' => true]);
        list($storage) = fn_get_storages(['storage_id' => $storage_id]);
        fn_write_r('runtime.storages:', Registry::get('runtime.storages'), 'runtime.current_storage:', Registry::get('runtime.current_storage'), 'delivery_date_by_storage:', $delivery_date_by_storage, 'fn_get_storages:', $storages, 'get_storage_data:', $storage);

        // 2. notify us and baltika
        $mailer = Tygh::$app['mailer'];
        $mailer->send(array(
            'to' => array('support@i-sd.ru', 'k.frank@i-sd.ru', 'lysenko_kn@baltika.com'),
            'from' => 'default_company_orders_department',
            'data' => array('data' => $data),
            'subject' => 'i-sd.ru: Empty storage id!',
            'body' => "Внимание! Размещен заказ без признака склада от user_id " . $order['user_id'] . ' ' . $order['firstname'] . ' ' . $order['email'],
        ), 'A'); 
    }
}
