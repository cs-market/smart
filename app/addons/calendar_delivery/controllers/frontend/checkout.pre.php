<?php

use Tygh\Registry;
use Tygh\Storage;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$cart = & Tygh::$app['session']['cart'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!empty($_REQUEST['delivery_date']) && is_array($_REQUEST['delivery_date'])) {
		$delivery_date = $_REQUEST['delivery_date'];
		foreach ($delivery_date as $company_id => $date) {
			$res = true;
			$choosed_ts = fn_parse_date($date, true);
			$c_data = fn_get_company_data($company_id);

			$compare_ts = fn_ts_this_day(strtotime('+1 day'));
			if ((date('H') >= '16' && date('i') >= '30' ) && $c_data['after17rule'] != 'Y') {
				$compare_ts = fn_ts_this_day(strtotime('+2 days'));
			}

			if ($choosed_ts < $compare_ts) {
				$res = false;
			}
			if ($c_data['sunday_shipping'] == 'N' && date('w', $choosed_ts) == 0) {
				$res = false;
			}
			if ($c_data['saturday_rule'] == 'N' && date('w', $choosed_ts) == 1 && ((date('w', time()) == 0) || (date('w', time()) == 6 && date('H', time() >= 16 )))) {
				$res = false;
			}

			if (!$res) {
				if (count($cart['product_groups']) > 1)
					fn_set_notification('N', __('notice'), __('calendar_delivery.choose_another_day_vendor') . ' ' . $c_data['company']);
				else {
					fn_set_notification('N', __('notice'), __('calendar_delivery.choose_another_day'));
				}
				$_REQUEST['next_step'] = 'ster_three';
			}
		}

		$cart['delivery_date'] = $_REQUEST['delivery_date'];
	}
}