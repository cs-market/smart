<?php 

use Tygh\Registry;

if ($mode == 'cron') {
	$condition = db_quote(' AND autoload_csv = ?s', 'Y');
	if (!empty($action)) {
		$condition .= db_quote(' AND company_id = ?i', $action);
	}
	$companies = db_get_fields("SELECT company_id FROM ?:companies WHERE 1 $condition");
	foreach ($companies as $company_id) {
		$files = fn_exim_csv_find_csvs($company_id);
		fn_exim_csv_run_import($files, $company_id);
	}
	exit ;
}

// if ($mode == 'run_import') {
// 	//unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
// 	if (empty($_SERVER['PHP_AUTH_USER'])) {
// 		header('WWW-Authenticate: Basic realm="Authorization required"');
// 		header('HTTP/1.0 401 Unauthorized');
// 	}
// 	if (!empty($_SERVER['PHP_AUTH_USER'])) {
// 		$data['user_login'] = $_SERVER['PHP_AUTH_USER'];
// 		list($status, $user_data, $user_login, $password, $salt) = fn_auth_routines($data, array());

// 		if ($user_login != $_SERVER['PHP_AUTH_USER'] || empty($user_data['password']) || $user_data['password'] != fn_generate_salted_password($_SERVER['PHP_AUTH_PW'], $salt)) {

// 			fn_echo("Error in login or password user");
// 			unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
// 		} else {
// 			// import body
// 			$params = $_REQUEST;
// 			if ($user_data['user_type'] == 'V') {
// 				if ($user_data['company_id'] != 0) {
// 					$company_id = $user_data['company_id'];
// 					Registry::set('runtime.company_id', $company_id);
// 				}
// 			} else {
// 				Registry::set('runtime.company_id', $company_id);
// 			}
// 			if (isset($params['preset_id'])) {
// 				if (Registry::get('runtime.company_id')) {
// 					$cond = fn_get_company_condition('company_id', true, '', false, true);
// 				}
// 				$preset_id = db_get_field("SELECT preset_id FROM ?:import_presets WHERE preset_id = ?i $cond", $params['preset_id']);
// 				if (empty($preset_id)) {
// 					fn_echo("Import not found");
// 				} else {
// 					fn_run_import($preset_id);
// 				}
// 			}

// 			if (!empty($params['type']) && !empty($params['file'])) {
// 				$pattern = fn_exim_get_pattern_definition($params['type'], 'import');
// 				if ($pattern) {
// 					$default_params = array(
// 						'delimiter' => ';',
// 						'images_path' => 'images/',
// 						'price_dec_sign_delimiter' => '.',
// 						'category_delimiter' => '///',
// 						'skip_creating_new_products' => 'N'
// 					);
// 					if (is_array($params)) {
// 						$params = array_merge($default_params, $params);
// 					} else {
// 						$params = $default_params;
// 					}
// 					if ($params['delimiter'] == ',') {
// 						$params['delimiter'] = 'C';
// 					}

// 					Registry::set('runtime.skip_area_checking', true);

// 					if (($data = fn_exim_get_csv($pattern, fn_get_files_dir_path().$params['file'], $params))) {

// 						if (fn_import($pattern, $data, $params)) {
// 							fn_echo('Success');
							
// 							if (isset($params['unset_file']) && $params['unset_file'] == 'true') {
// 								unlink(fn_get_files_dir_path().$params['file']);
// 							}
// 						}
// 					}
// 				} else {
// 					fn_echo("Unexpected import type");
// 				}
// 			}
// 		}
// 	} else {
// 		fn_echo("Enter login and password user");
// 	}
// 	exit;
// }