<?php

use Tygh\Registry;
use Tygh\Http;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_monolith_place_order($order_id, $action, $order_status, $cart, $auth) {
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
				date("Y-m-d\TH:i:s", $order_info['timestamp']),
				1,
				1,
				date("Y-m-d\TH:i:s", $order_info['timestamp']),
			),
		),
	);

	$monolith_order['d'][] = array(
		'@attributes' => array ('name' => 'CRMOrder'),
		'r' => array(
			'f' => array(
				$addon['order_prefix'] . $order_id,
				date("Y-m-d\TH:i:s", $order_info['timestamp']),
				'', //CompanyId
				$order_info['fields']['38'], //AddressId
				'',
				$order_info['user_id'],
				date("Y-m-d\TH:i:s", $order_info['timestamp']),
				'CustOrder',
				'Entered',
			),
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
					'smart.wh1',
					'',
					$p['product_code'],
					'ea',
					$p['price'],
					$p['amount']
				),
			),
		);
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

	$xml = fn_render_xml_from_array($schema);
	if ($action == 'print') {
		fn_print_die($xml);
	}
	$result = HTTP::POST($addon['environment_url'], array('XMLData'=>$xml));
}