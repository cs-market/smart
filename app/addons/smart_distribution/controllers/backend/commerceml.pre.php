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

$path_file = 'exim/1C_' . date('dmY') . '/';
$path = fn_get_files_dir_path() . $path_file;
$path_commerceml = fn_get_files_dir_path();
$log = new Logs($path_file, $path);
$company_id = fn_get_runtime_company_id();
$exim_commerceml = new SDRusEximCommerceml(Tygh::$app['db'], $log, $path_commerceml);
$_data = $_data ?? [];
list($status, $user_data, $user_login, $password, $salt) = fn_auth_routines($_data, array());
$exim_commerceml->import_params['user_data'] = $auth;

list($cml, $s_commerceml) = $exim_commerceml->getParamsCommerceml();
$s_commerceml = $exim_commerceml->getCompanySettings();

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
