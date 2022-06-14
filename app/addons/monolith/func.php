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
        ($order_info['company_id'] == '1804') ? 'CustReturn' : 'CustOrder',
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
	foreach ($order_info['products'] as $p) {
		$iterator += 1;
		$monolith_order['d'][] = array(
			'@attributes' => array ('name' => 'CRMOrderLine'),
			'r' => array(
				'f' => array(
					$addon['order_prefix'] . $order_id,
					$iterator,
					// 'smart.wh1',
					// '',
					$p['product_code'],
					'ea',
					$p['price'],
					$p['amount']
				),
			),
		);
	}

	if (!empty($order_info['promotions'])) {
        $promotion_products = array_filter($order_info['products'], function($v) {return (isset($v['promotions']));});
        $product_promotions = [];
        foreach ($promotion_products as $product) {
            foreach($product['promotions'] as $promotion_id => $promotion) {
                if (!isset($product_promotions[$promotion_id])) {
                    $product_promotions[$promotion_id] = fn_get_promotion_data($promotion_id);
                }
                foreach ($promotion['bonuses'] as $value) {
                    $monolith_order['d'][] = array(
                        '@attributes' => array ('name' => 'CRMOrderDiscountLine'),
                        'r' => array(
                            'f' => array(
                                date("Y-m-d\T00:00:00", $order_info['timestamp']),
                                $addon['order_prefix'] . $order_id,
                                $product_promotions[$promotion_id]['external_id'],
                                $product_promotions[$promotion_id]['name'],
                                $value['discount'],
                                $product['product_code']
                            ),
                        ),
                    );
                }
            }
        }

        foreach($order_info['promotions'] as $promotion_id => $promotion) {
            if (in_array($promotion_id, array_keys($product_promotions))) continue;

            $promotion_data = fn_get_promotion_data($promotion_id);
            $monolith_order['d'][] = array(
                '@attributes' => array ('name' => 'CRMOrderDiscountLine'),
                'r' => array(
                    'f' => array(
                        date("Y-m-d\T00:00:00", $order_info['timestamp']),
                        $addon['order_prefix'] . $order_id,
                        $promotion_data['external_id'], // REPLACE BY PROMOTION EXTERNAL ID
                        $promotion_data['name'],
                        $order_info['subtotal_discount'],
                        'whole_order'
                    ),
                ),
            );
        }
	} else {
        unset($schema['extdata']['scheme']['data']['s']['d'][4]);
    }

	// if we have comment
	if (!empty($order_info['notes'])) {
		$monolith_order['d'][] = array(
			'@attributes' => array ('name' => 'CRMOrderOption'),
			'r' => array(
				'f' => array(
					$addon['order_prefix'] . $order_id, 'Comment', $order_info['notes'],
				)
			),
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
