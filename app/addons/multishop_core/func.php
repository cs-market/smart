<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

//fn_multishop_init_store_params_by_host($_REQUEST);

function fn_multishop_init_store_params_by_host(&$request, $area = AREA) {
    if ($area == 'A' && empty($request['allow_initialization'])) {
        return array(INIT_STATUS_OK);
    }
    $host = $_SERVER['HTTP_HOST'];
    $short_host = preg_replace('/^www[0-9]*\./i', '', $host);
    $field = defined('HTTPS') ? 'secure_storefront' : 'storefront';
    $conditions = db_quote('?p RLIKE ?l', $field, "^(www[0-9]*.)?{$short_host}(/|$)");
    $shops = db_get_array("SELECT shop_id, {$field} FROM ?:shops WHERE {$conditions}");

    if (!empty($shops)) {
        if (count($shops) == 1) {
            $request['switch_shop_id'] = $shops[0]['shop_id'];
        } else {
            $found_shops = array();
            foreach ($shops as $shop) {
                $parsed_url = parse_url('http://' . $shop[$field]); // protocol prefix does not matter

                if (empty($parsed_url['path'])) {
                    $found_shops[0] = $shop['shop_id'];

                } elseif (!empty($_SERVER['REQUEST_URI']) && preg_match("/^" . preg_quote($parsed_url['path'], '/') . "([\/\?].*?)?$/", $_SERVER['REQUEST_URI'], $m)) {
                    $priority = count(explode('/', $parsed_url['path']));
                    $found_shops[$priority] = $shop['shop_id'];
                }
            }

            if (!empty($found_shops)) {
                krsort($found_shops);
                $request['switch_shop_id'] = reset($found_shops);
            }
        }
 	}

 	if (!empty($request['switch_shop_id'])) {
 		$shop_data = db_get_row('SELECT shop_id, storefront, secure_storefront FROM ?:shops WHERE shop_id = ?i', $request['switch_shop_id']);
		$config = Registry::get('config');
		
		$config['origin_http_location'] = $config['http_location'];
		$config['origin_https_location'] = $config['https_location'];

		$url_data = fn_get_shop_urls(0, $shop_data);

		$config = fn_array_merge($config, $url_data);
		$config['images_path'] = $config['current_path'] . '/media/images/';

		Registry::set('config', $config);
 	}
}

function fn_get_shop_urls($shop_id, $shop_data = array())
{
    static $cache = array();
    $urls = array();

    $shop_id = !empty($shop_data) ? $shop_data['shop_id'] : $shop_id;

    if (!empty($shop_id)) {
        if (!empty($cache[$shop_id])) {
            $shop_data = $cache[$shop_id];
        }

        if (empty($shop_data)) {
            $shop_data = db_get_row('SELECT shop_id, storefront, secure_storefront FROM ?:companies WHERE shop_id = ?i', $shop_id);
        }

        fn_set_hook('get_storefront_urls', $shop_data);

        $cache[$shop_id] = $shop_data;

        $url = 'http://' . $shop_data['storefront'];
        $secure_url = 'https://' . $shop_data['secure_storefront'];

        $info = parse_url($url);
        $secure_info = parse_url($secure_url);

        $http_path = !empty($info['path']) ? str_replace('\\', '/', $info['path']) : '';
        $http_path = rtrim($http_path, '/');

        $https_path = !empty($secure_info['path']) ? str_replace('\\', '/', $secure_info['path']) : '';
        $https_path = rtrim($https_path, '/');

        $http_host = !empty($info['port']) ? $info['host'] . ':' . $info['port'] : $info['host'];
        $https_host = !empty($secure_info['port']) ? $secure_info['host'] . ':' . $secure_info['port'] : $secure_info['host'];

        $current_host = (defined('HTTPS')) ? $https_host : $http_host;
        $current_path = (defined('HTTPS')) ? $https_path : $http_path;

        $http_location = 'http://' . $http_host . $http_path;
        $https_location = 'https://' . $https_host . $https_path;

        $current_location = defined('HTTPS') ? $https_location : $http_location;

        $urls = array(
            'http_host' => $http_host,
            'http_path' => $http_path,
            'http_location' => $http_location,

            'https_host' => $https_host,
            'https_path' => $https_path,
            'https_location' => $https_location,

            'current_host' => $current_host,
            'current_path' => $current_path,
            'current_location' => $current_location
        );
    }

    return $urls;
}

function fn_multishop_core_init_company_id(&$params, $company_id, $available_company_ids, $result) {


    if (isset($params['switch_shop_id'])) {
        $switch_shop_id = intval($params['switch_shop_id']);
        unset($params['switch_shop_id']);
    } else {
        $switch_shop_id = false;
    }

        // set company_id for vendor's admin
/*    if (AREA == 'A' && !empty(Tygh::$app['session']['auth']['company_id'])) {
        $company_id = intval(Tygh::$app['session']['auth']['company_id']);
        $available_company_ids = array($company_id);
        if (!fn_get_available_company_ids($company_id)) {
            return fn_init_company_id_redirect($params, 'access_denied');
        }
    }
*/
    //fn_print_die('develop here', $switch_shop_id, $params, $company_id, $available_company_ids, $result);
    // admin switching company_id
    if ($switch_shop_id !== false) { // request not empty
/*        if ($switch_shop_id) {
            if (fn_get_available_company_ids($switch_shop_id)) {
                $shop_id = $switch_shop_id;
            } else {
                return fn_init_company_id_redirect($params, 'company_not_found');
            }
        }*/
        fn_set_session_data('shop_id', $switch_shop_id, COOKIE_ALIVE_TIME);
    } else {
        $switch_shop_id = intval(fn_get_session_data('shop_id'));
    }
    if ($switch_shop_id) {
        Registry::set('runtime.shop_id', $switch_shop_id);
        Registry::set('runtime.shop_usergroups', explode(',', db_get_field('SELECT usergroup_ids FROM ?:shops WHERE shop_id = ?i', $switch_shop_id)));
    }
}
