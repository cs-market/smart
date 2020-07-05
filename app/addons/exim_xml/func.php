<?php

use Tygh\Commerceml\SDRusEximCommerceml;
use Tygh\Commerceml\Logs;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_exim_xml_exim_csv_find_import_files_post(&$files, $cid, $dir) {
	$fs_files = fn_get_dir_contents($dir, false, true, 'xml');

	$priority = array('import' => 10, 'offers' => 20, 'orders' => 30);
	foreach ($fs_files as $file) {
		$data = pathinfo($file);
		list($data['import_object'], $tmp) = explode('.', $data['filename']);
		$data['dirname'] = $dir;
		$data['priority'] = $priority[$data['import_object']];
		$data['extension'] = 'xml';
		$files[] = $data;
	}
}

function fn_exim_xml_exim_csv_import_file($import, $company_id) {
	if ($import['extension'] == 'xml') {
		$path_commerceml = $import['dirname'];
		$log = new Logs('', $path_commerceml);
		Registry::set('runtime.company_id', $company_id);

		$exim_commerceml = new SDRusEximCommerceml(Tygh::$app['db'], $log, $path_commerceml);
		$manual = true;
		//unset($_SESSION['exim_1c']);
		$lang_code = (!empty($s_commerceml['exim_1c_lang'])) ? $s_commerceml['exim_1c_lang'] : CART_LANGUAGE;

		$exim_commerceml->import_params['lang_code'] = $lang_code;
		$exim_commerceml->import_params['manual'] = true;
		list($cml, $s_commerceml) = $exim_commerceml->getParamsCommerceml();
		$exim_commerceml->company_id = $company_id;

		if ($import['import_object'] == 'import') {
			$filename = $import['basename'];
			list($xml, $d_status, $text_message) = $exim_commerceml->getFileCommerceml($filename);

			fn_print_die($xml);
			$exim_commerceml->addMessageLog($text_message);

			if ($d_status === false) {
				fn_echo("failure");
				exit;
			}

			if ($s_commerceml['exim_1c_import_products'] != 'not_import') {
				$exim_commerceml->importDataProductFile($xml);
			} else {
				fn_echo("success\n");
			}
		}
		if ($action == 'offers') {
			$filename = $import['basename'];
			list($xml, $d_status, $text_message) = $exim_commerceml->getFileCommerceml($filename);
			$exim_commerceml->addMessageLog($text_message);
			if ($d_status === false) {
				fn_echo("failure");
				exit;
			}
			if ($s_commerceml['exim_1c_only_import_offers'] == 'Y') {
				$exim_commerceml->importDataOffersFile($xml, $service_exchange, $lang_code, $manual);
			} else {
				fn_echo("success\n");
			}
		}
		if ($import['import_object'] == 'orders') {
			$filename = $import['basename'];
            list($xml, $d_status, $text_message) = $exim_commerceml->getFileCommerceml($filename);
            $exim_commerceml->addMessageLog($text_message);
            if ($d_status === false) {
                fn_echo("failure");
                exit;
            }

            $exim_commerceml->importFileOrders($xml, $lang_code);
		}
	}
}

function fn_exim_xml_place_order($order_id, $action, $order_status, $cart, $auth) {
	$order = fn_get_order_info($order_id);
	$company_id = $order['company_id'];
	fn_define('DB_LIMIT_SELECT_ROW', 30);
	if (db_get_field('SELECT export_order_to_xml FROM ?:companies WHERE company_id = ?i', $order['company_id']) == 'Y') {
		$path_commerceml = fn_get_files_dir_path($company_id) . 'output/';
		$log = new Logs('', $path_commerceml);
		Registry::set('runtime.company_id', $company_id);

		$exim_commerceml = new SDRusEximCommerceml(Tygh::$app['db'], $log, $path_commerceml);
		$manual = true;
		//unset($_SESSION['exim_1c']);
		$lang_code = (!empty($s_commerceml['exim_1c_lang'])) ? $s_commerceml['exim_1c_lang'] : CART_LANGUAGE;

		$exim_commerceml->import_params['lang_code'] = $lang_code;
		$exim_commerceml->import_params['manual'] = true;
		list($cml, $s_commerceml) = $exim_commerceml->getParamsCommerceml();
		$exim_commerceml->company_id = $company_id;

		// ob_start();
		// ob_start();
		if ($s_commerceml['exim_1c_all_product_order'] == 'Y') {
            $exim_commerceml->exportAllProductsToOrders($lang_code);
        } else {
            $xml = $exim_commerceml->exportDataOrdersGetXML($lang_code);
        }
        /*ob_get_clean();
        $xml = ob_get_contents();
		fn_write_r($xml);
        fn_clear_ob();
        header_remove();
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-type: text/html; charset=utf-8");*/
		fn_put_contents($path_commerceml."order.$order_id.xml", $xml);
	}
}