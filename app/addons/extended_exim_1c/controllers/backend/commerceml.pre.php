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

use Tygh\Commerceml\ExRusEximCommerceml;
use Tygh\Commerceml\Logs;
use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

$path_file = 'exim/1C_' . date('dmY') . '/';
$path = fn_get_files_dir_path() . $path_file;
$path_commerceml = fn_get_files_dir_path();
$log = new Logs($path_file, $path);
$company_id = fn_get_runtime_company_id();
$exim_commerceml = new ExRusEximCommerceml(Tygh::$app['db'], $log, $path_commerceml);
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

if ($mode == 'sync') {
    $params = $_REQUEST;

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
    if ($action == 'export_orders') {
        if ($s_commerceml['exim_1c_all_product_order'] == 'Y') {
            $exim_commerceml->exportAllProductsToOrders($lang_code);
        } else {
            $exim_commerceml->exportDataOrders($lang_code);
        }
    }
    fn_print_die('done');
}
