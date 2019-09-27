<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

use Tygh\Commerceml\SDRusEximCommerceml;
use Tygh\Commerceml\Logs;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$suffix = '';

	if ($mode == 'sd_save_offers_data') {
		if ($s_commerceml['exim_1c_create_prices'] == 'Y') {
			$prices = $_REQUEST['prices_1c'];
			if (!empty($_REQUEST['list_price_1c'])) {
				$_list_prices = fn_explode(',', $_REQUEST['list_price_1c']);
				$list_prices = array();
				foreach($_list_prices as $_list_price) {
					$list_prices[] = array(
							'price_1c' => trim($_list_price),
							'usergroup_id' => 0,
							'type' => 'list',
							'company_id' => $company_id
					);
				}
				$prices = fn_array_merge($list_prices, $prices, false);
			}

			$base_prices = array();
			if (!empty($_REQUEST['base_price_1c'])) {
				$_base_prices = fn_explode(',', $_REQUEST['base_price_1c']);
				foreach($_base_prices as $_base_price) {
					$base_prices[] = array(
						'price_1c' => trim($_base_price),
						'usergroup_id' => 0,
						'type' => 'base',
						'company_id' => $company_id
					);
				}
			}
			$prices = fn_array_merge($base_prices, $prices, false);

			db_query("DELETE FROM ?:rus_exim_1c_prices WHERE company_id = ?i", $company_id);
			foreach ($prices as $price) {
				if (!empty($price['price_1c'])) {
					$price['company_id'] = $company_id;
					db_query("INSERT INTO ?:rus_exim_1c_prices ?e", $price);
				}
			}
		}

		return array(CONTROLLER_STATUS_REDIRECT, 'commerceml.offers');
	}
}


if ($mode == 'sync') {
	$path_file = 'exim/1C_' . date('dmY') . '/';
	$path = fn_get_files_dir_path() . $path_file;
	$path_commerceml = fn_get_files_dir_path();

	$log = new Logs($path_file, $path);

	$exim_commerceml = new SDRusEximCommerceml(Tygh::$app['db'], $log, $path_commerceml);
	list($status, $user_data, $user_login, $password, $salt) = fn_auth_routines($_data, array());
	$exim_commerceml->import_params['user_data'] = $auth;

	list($cml, $s_commerceml) = $exim_commerceml->getParamsCommerceml();
	$s_commerceml = $exim_commerceml->getCompanySettings();

	$params = $_REQUEST;
	$company_id = fn_get_runtime_company_id();

	$manual = true;
	//unset($_SESSION['exim_1c']);
	$lang_code = (!empty($s_commerceml['exim_1c_lang'])) ? $s_commerceml['exim_1c_lang'] : CART_LANGUAGE;

	$exim_commerceml->getDirCommerceML();
	$exim_commerceml->import_params['lang_code'] = $lang_code;
	$exim_commerceml->import_params['manual'] = true;
	$exim_commerceml->company_id = Registry::get('runtime.company_id');
	if ($action == 'import') {
		$filename = (!empty($params['filename'])) ? fn_basename($params['filename']) : 'import.xml';
		$fileinfo = pathinfo($filename);
		list($xml, $d_status, $text_message) = $exim_commerceml->getFileCommerceml($filename);
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
		$filename = (!empty($params['filename'])) ? fn_basename($params['filename']) : 'offers.xml';
		$fileinfo = pathinfo($filename);
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
	fn_print_die('done');
} elseif ($mode == 'base_price' && $action) {
	list($products,) = fn_get_products(['company_id' => $action]);
	$auth = $_SESSION['auth'];
	foreach ($products as $product_id => $p) {
		$product = fn_get_product_data($product_id, $auth, DESCR_SL, '', false, false, false, true);
		if (count(($product['prices'])) > 1) {
				fn_print_die($product['prices'], $product_id);
				$prices = array_column($product['prices'], 'price');
				$price = max($prices);
				$product['price'] = $price;
				fn_update_product($product, $product_id, DESCR_SL);
		}
	}
	fn_print_die('done');
} elseif ($mode == 'replace_manager') {
	list($users) = fn_get_users(array('managers' => 1132));
	$counter = 0;
	foreach ($users as $user) {
		$managers = db_get_fields('SELECT vendor_manager FROM ?:vendors_customers WHERE customer_id = ?i', $user['user_id']);
		if ($managers && in_array('1132', $managers) && !in_array('3760', $managers)) {
			$counter += 1;
			$udata = array('customer_id' => $user['user_id'], 'vendor_manager' => 3760);
			db_query('INSERT INTO ?:vendors_customers ?e', $udata);
			db_query('DELETE FROM ?:vendors_customers WHERE customer_id = ?i AND vendor_manager = ?i', $user['user_id'], 1132);
		}
	}
	fn_print_die('done', $counter);
} elseif ($mode == 'pservice_sku') {
	$params = array('company_id' => 28);
	list($products, ) = fn_get_products($params);
	foreach ($products as $pid => $product) {
		$pcode = trim($product['product_code']);
		if (strlen($pcode) < 11) {
				$pcode = str_pad($pcode, 11, "0", STR_PAD_LEFT);
				db_query('UPDATE ?:products SET product_code = ?s WHERE product_id = ?i;', $pcode, $pid);
		}
	}
	fn_print_die('stop');
} elseif ($mode == 'get_profiles') {
	$report = db_get_array("SELECT up.user_id, count(profile_id) as count, firstname, lastname, phone, email FROM ?:user_profiles AS up LEFT JOIN ?:users AS u ON u.user_id = up.user_id GROUP BY user_id HAVING count(profile_id) > 1 ");
	$params['filename'] = 'profiles.csv';
	$params['force_header'] = true;
	$export = fn_exim_put_csv($report, $params, '"');
} elseif ($mode == 'devide_pinta') {
	$file = 'var/files/pinta1.csv';
	$content = fn_exim_get_csv(array(), $file, array('validate_schema'=> false) );
	$sku = array_column($content, 'Номенклатура.Код');
	array_walk($sku, 'fn_trim_helper');
	//list($pinta_products, ) = fn_get_products(array('company_id' => 41));
	//fn_print_die(count($pinta_products));
	//$products = db_get_hash_single_array('SELECT product_id, product_code FROM ?:products WHERE product_code IN (?a) AND company_id = ?i', array('product_id', 'product_code'), $sku, 41);
	$products = db_get_fields('SELECT product_code FROM ?:products WHERE product_code IN (?a) AND company_id = ?i',  $sku, 41);
	$unexist_products = array_diff($sku, $products);
	fn_print_die($sku, $products, $unexist_products, count($unexist_products), count($products));
	//fn_print_die('here');
} elseif ($mode == 'delete_pinta') {
	$pids = db_get_fields("SELECT product_id FROM ?:products WHERE 1 AND company_id in (?a)", array('41', '46'));
	$counter = 0;
	foreach ($pids as $pid) {
		if (fn_delete_product($pid)) {
			$counter += 1;
		}
	}
	fn_print_die($counter);
}

function fn_merge_products($company_id = 13)
{

  fn_echo('Start');
  fn_echo('<br />');

  $exclude_cid = [642, 538]; //exclude products`category

  list($exclude_products) = fn_get_products(['cid' => $exclude_cid]); //exclude products`category
	$exclude_products = array_keys($exclude_products);

  //  get products with dublicate pr_code
  $product_groups = db_get_hash_multi_array("SELECT A.product_id, A.product_code
	FROM ?:products A
	INNER JOIN (SELECT product_id, product_code, company_id
		FROM ?:products
		WHERE company_id = ?i
		GROUP BY product_code
		HAVING COUNT(*) > 1) B
	ON A.product_code = B.product_code AND A.company_id = B.company_id",
	['product_code', 'product_id'],
	$company_id);
  if (!$product_groups) {
	fn_echo('Did not find products');
	die();
  }

  foreach ($product_groups as $product_code => $products_info) {

	fn_echo('Process prodcut code: '  . $product_code);
	fn_echo('<br />');

	$product_ids = array_keys($products_info);
	$main_product_id = '';
	$new_data = [
		'additional_categories' => [], // from main & additional
		'price' => 0, // max price
		'usergroup_ids' => [], // доступность юзергруппе
		'prices' => [0], // mix pr qty discount
		// 'qty' => 0, // use main products qty
		// 'image' => [], // use main
		// 'name' => '', //Если в названии итогового товара есть [CLONE],  [CLONE] [CLONE], это надо подтереть
		// остальные товары удаляются.
	];
	list($products) = fn_get_products(['pid' => $product_ids]);

	fn_gather_additional_products_data($products, array('get_icon' => false, 'get_detailed' => true, 'get_options' => false, 'get_discounts' => false));

	foreach ($products as $product_id => $product) {

		//  check exclude products
		if (in_array($product_id, $exclude_products)) {
		if(($key = array_search($product_id, $product_ids)) !== false){
			unset($product_ids[$key]);
		}

		continue;
		}

		if (isset($product['main_pair']) && !empty($product['main_pair']) && empty($main_product_id)) {
		$main_product_id = $product_id;
		}

		$new_data['additional_categories'] = array_merge($new_data['additional_categories'], $product['category_ids']);

		$new_data['price'] = max($product['price'], $new_data['price']);

		foreach (explode(',', $product['usergroup_ids']) as $user_group) {
		$new_data['usergroup_ids'][] = $user_group;
		$new_data['prices'][] = [
			'lower_limit' => '1',
			'price' => $product['price'],
			'type' => 'A',
			'usergroup_id' => $user_group
		];
		}
		unset($new_data['prices'][0]);
	}

	$new_data['additional_categories'] = array_unique($new_data['additional_categories']);
	$new_data['usergroup_ids'] = array_unique($new_data['usergroup_ids']);

	$main_product = $products[$main_product_id];

	//  remove clone label
	$main_product['product'] = trim(str_replace('[CLONE]', '', $main_product['product']));

	//  some warning on the yml_export add-on
	$main_product['yml2_delivery_options'] = (
		isset($main_product['yml2_delivery_options'])
		&& gettype ($main_product['yml2_delivery_options']) !== 'string')
		? $main_product['yml2_delivery_options']
		: [$main_product['yml2_delivery_options']];

	$main_product = array_merge($main_product, $new_data);
	$product_id = fn_update_product($main_product, $main_product_id, DESCR_SL);


	if ($product_id) {
		fn_echo('Update product #' . $main_product_id);
		fn_echo('<br />');

		//  remove other products
		unset($product_ids[array_search($main_product_id, $product_ids)]);

		foreach ($product_ids as $delete_pr_id) {
		$result = fn_delete_product($delete_pr_id);
		if ($result) {
			fn_echo('Deleted product #' . $delete_pr_id);
		} else {
			fn_echo('Problem to delete product #' . $delete_pr_id);
		}
		}
	} else {
		fn_echo('Problem to save product #' . $main_product_id);
	}

	fn_echo('<hr />');
	
  }

  fn_echo("C'est finit");
  exit;
}
