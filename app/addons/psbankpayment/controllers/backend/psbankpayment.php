<?php
use Tygh\Registry;
use Tygh\Api\Request;
use Tygh\Payments\Processors\PSBankLib;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if($mode == 'action') {
		$nonce = fn_psbankpayment_send_request($_REQUEST);
		$exit = 0;
	    $counter = 20;
	    do {
	        sleep(1);
	        $data = db_get_row('SELECT TRTYPE, RESULT, RCTEXT FROM ?:psbankpayment WHERE ORDER_ID="'.intval($_REQUEST['order_id']).'" AND NONCE=?s', $nonce);
	        if ($data && $data['TRTYPE'] == $_REQUEST['trtype']) {
	            $exit = 1;
	            if($data['RESULT'] == 0 ){
	                $action_success = true;
	            }
	        }
	        $counter--;
	    } while(!$exit && $counter);
	    if(!$action_success){
	        if ($exit) {
	            fn_set_notification('N', __('notice'), __('addons.psbankpayment.action_fail_text',['rctext' => $data['RCTEXT']]));
	        } else {
	            fn_set_notification('N', __('notice'), __('addons.psbankpayment.action_fail'));
	        }
	    } else {
	    	fn_set_notification('N', __('notice'), __('addons.psbankpayment.action_success'));
	    }

		return array(CONTROLLER_STATUS_OK, "psbankpayment.transactions");
	}
} else {
	if ($mode == 'transactions') {

		$items_per_page = Registry::get('settings.Appearance.admin_elements_per_page');

		$default_params = array (
			'page' => 1,
			'items_per_page' => $items_per_page
		);

		$params = array_merge($default_params, $_REQUEST);
		$where = array();
		$search = array();
		if(!empty($params['filter_order_id'])) {
			$where[] = ' ORDER_ID='.intval($params['filter_order_id']);
			$search['order_id'] = $params['filter_order_id'];
		}
		if(!empty($params['filter_rrn'])) {
			$where[] = db_quote(" RRN=?s ", $params['filter_rrn']);
			$search['rrn'] = $params['filter_rrn'];
		}
		if ($where) {
			$where = "WHERE ".implode(" and ", $where);
		} else {
			$where = '';
		}
		$limit = '';
		if (!empty($params['items_per_page'])) {
			$params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:psbankpayment $where");
			$limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
		}
		$data = db_get_array('SELECT * FROM ?:psbankpayment '.$where.' ORDER BY ORDER_ID DESC '.$limit);
		Tygh::$app['view']->assign('transactions', $data);
		Tygh::$app['view']->assign('search', $search);
	}
	if ($mode == 'history') {
		$data = db_get_array('SELECT * FROM ?:psbankpayment_history WHERE `ORDER`=?i ', $_REQUEST['order_id']);
		Tygh::$app['view']->assign('transactions', $data);
		Tygh::$app['view']->assign('order_id', $_REQUEST['order_id']);
	}
}
