<?php

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
            foreach($product['promotions'] as $promotion_id => $promotion) {
                if (!isset($applied_promotions[$promotion_id])) {
                    $applied_promotions[$promotion_id] = fn_get_promotion_data($promotion_id);
                }
               
                foreach ($promotion['bonuses'] as $value) {
                    list($external_id) = explode('.', $applied_promotions[$promotion_id]['external_id']);
                    $discount_value = $value['discount'];
                    $discount_unit = 'руб';
                    $discount_price = $value['discount_value'];

                    if ($value['discount_bonus'] == 'by_fixed') {
                        $discount_price = $product['price'];
                    } elseif (in_array($value['discount_bonus'], ['by_percentage', 'to_percentage'])) {
                        $discount_value = $value['discount_value'];
                        $discount_unit = '%';
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
                            $applied_promotions[$promotion_id]['name'],
                            $discount_value,
                            $discount_unit,
                            $discount_price,
                            $product['product_code']
                        )
                    ];
                }
                break;
            }
        }

        // foreach($order_info['promotions'] as $promotion_id => $promotion) {
        //     if (in_array($promotion_id, array_keys($applied_promotions))) continue;

        //     $applied_promotions[$promotion_id] = fn_get_promotion_data($promotion_id);
        //     list($external_id) = explode('.', $applied_promotions[$promotion_id]['external_id']);
        //     $CRMOrderDiscountLine[] = [
        //         'f' => array(
        //             date("Y-m-d\T00:00:00", $order_info['timestamp']),
        //             $addon['order_prefix'] . $order_id,
        //             $external_id,
        //             $applied_promotions[$promotion_id]['name'],
        //             $order_info['subtotal_discount'],
        //             'whole_order'
        //         )
        //     ];
        // }
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
                'CRMOrdPaymTyp', 
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
    $result = true;
    if (empty(trim($response))) {
        return false;
    }
    $data = json_decode(json_encode(simplexml_load_string(str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $response))), 1);
    $xml_response = $data['Body']['RequestResponse']['RequestResult'];
    $result = simplexml_load_string($xml_response);
    if (isset($result->error) || isset($result->scheme->error)) {
        return false;
    } else {
        return true;
    }
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
