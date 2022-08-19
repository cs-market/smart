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

    $d_record = [
        $addon['order_prefix'] . $order_id,
        date("Y-m-d\T00:00:00", $order_info['timestamp']),
        //'', //CompanyId
        $order_info['fields']['38'], //CRMClientId
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
        $iterator += 1;
        $CRMOrderLine[] = [
            'f' => array(
                $addon['order_prefix'] . $order_id,
                $iterator,
                // 'smart.wh1',
                // '',
                $p['product_code'],
                'ea',
                $p['price'],
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
                    $CRMOrderDiscountLine[] = [
                        'f' => array(
                            date("Y-m-d\T00:00:00", $order_info['timestamp']),
                            $addon['order_prefix'] . $order_id,
                            $applied_promotions[$promotion_id]['external_id'],
                            $applied_promotions[$promotion_id]['name'],
                            $value['discount'],
                            $product['product_code']
                        )
                    ];
                }
            }
        }

        foreach($order_info['promotions'] as $promotion_id => $promotion) {
            if (in_array($promotion_id, array_keys($applied_promotions))) continue;

            $applied_promotions[$promotion_id] = fn_get_promotion_data($promotion_id);
            $CRMOrderDiscountLine[] = [
                'f' => array(
                    date("Y-m-d\T00:00:00", $order_info['timestamp']),
                    $addon['order_prefix'] . $order_id,
                    $applied_promotions[$promotion_id]['external_id'], // REPLACE BY PROMOTION EXTERNAL ID
                    $applied_promotions[$promotion_id]['name'],
                    $order_info['subtotal_discount'],
                    'whole_order'
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

    $CRMOrderOption = [
        [
            'f' => array(
                $addon['order_prefix'] . $order_id, 
                'PrZakaz', 
                ($order_info['documents_originals']) ? 1 : 0
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

    $result = HTTP::POST($addon['environment_url'], $soap, array(
        'headers' => array(
            'Content-type: application/soap+xml; charset=utf-8'
        ))
    );
    return $result;
}

function fn_monolith_place_order_post($cart, $auth, $action, $issuer_id, $parent_order_id, $order_id, $order_status, $short_order_data, $notification_rules) {
    if (count($cart['product_groups']) == 1) {
        $xml = fn_monolith_generate_xml($order_id);
        if (!empty($xml)) {
            if (fn_monolith_send_xml($xml)) {
                fn_change_order_status($order_id, 'A');
            }
        } 
    }
}

// duplicate from frontend controller for mobile application
function fn_monolith_allow_place_order_post(&$cart) {
    if (empty($cart['user_data']['email'])) {
        $cart['user_data']['email'] = fn_checkout_generate_fake_email_address($cart['user_data'], TIME);
    }
}
