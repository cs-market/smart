<?php 

use Tygh\Registry;
use Tygh\Addons\AdvancedImport\Exceptions\FileNotFoundException;
use Tygh\Addons\AdvancedImport\Exceptions\ReaderNotFoundException;
use Tygh\Exceptions\PermissionsException;
use Tygh\Enum\Addons\AdvancedImport\ImportStatuses;

if ($mode == 'run_import') {
	//unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
	if (empty($_SERVER['PHP_AUTH_USER'])) {
		header('WWW-Authenticate: Basic realm="Authorization required"');
		header('HTTP/1.0 401 Unauthorized');
	}
	if (!empty($_SERVER['PHP_AUTH_USER'])) {
		$data['user_login'] = $_SERVER['PHP_AUTH_USER'];
		list($status, $user_data, $user_login, $password, $salt) = fn_auth_routines($data, array());

		if ($user_login != $_SERVER['PHP_AUTH_USER'] || empty($user_data['password']) || $user_data['password'] != fn_generate_salted_password($_SERVER['PHP_AUTH_PW'], $salt)) {

			fn_echo("Error in login or password user");
			unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
		} else {
			// import body
			$params = $_REQUEST;
			if ($user_data['user_type'] == 'V') {
				if ($user_data['company_id'] != 0) {
					$company_id = $user_data['company_id'];
					Registry::set('runtime.company_id', $company_id);
				}
			} else {
				Registry::set('runtime.company_id', $company_id);
			}
			if (isset($params['preset_id'])) {
				if (Registry::get('runtime.company_id')) {
					$cond = fn_get_company_condition('company_id', true, '', false, true);
				}
				$preset_id = db_get_field("SELECT preset_id FROM ?:import_presets WHERE preset_id = ?i $cond", $params['preset_id']);
				if (empty($preset_id)) {
					fn_echo("Import not found");
				} else {
					fn_run_import($preset_id);
				}
			}
			if (!empty($params['type']) && !empty($params['file'])) {
				$pattern = fn_exim_get_pattern_definition($params['type'], 'import');
				if ($pattern) {
					if (empty($params['delimiter'])) {
						$params['delimiter'] = ';';
					} elseif ($params['delimiter'] == ',') {
						$params['delimiter'] = 'C';
					}

	                if (($data = fn_exim_get_csv($pattern, fn_get_files_dir_path().$params['file'], $params))) {
	                    if (fn_import($pattern, $data, $params)) {
	                    	fn_echo('Success');
	                    	if ($params['unset_file']) {
	                    		unlink(fn_get_files_dir_path().$params['file']);
	                    	}
	                    }
	                }
				} else {
					fn_echo("Unexpected import type");
				}
			}
		}
	} else {
		fn_echo("Enter login and password user");
	}
	exit;
}


function fn_run_import($preset_id) {
	/** @var \Tygh\Addons\AdvancedImport\Presets\Manager $presets_manager */
	$presets_manager = Tygh::$app['addons.advanced_import.presets.manager'];
	/** @var \Tygh\Addons\AdvancedImport\Presets\Importer $presets_importer */
	$presets_importer = Tygh::$app['addons.advanced_import.presets.importer'];

	list($presets,) = $presets_manager->find(false, array('ip.preset_id' => $preset_id), false);

	if ($presets) {

		Registry::set('runtime.advanced_import.in_progress', true, true);

		$preset = reset($presets);

		/** @var \Tygh\Addons\AdvancedImport\Readers\Factory $reader_factory */
		$reader_factory = Tygh::$app['addons.advanced_import.readers.factory'];


		$is_success = false;
		try {
			$reader = $reader_factory->get($preset);

			$fields_mapping = $_REQUEST['fields'] ?: $presets_manager->getFieldsMapping($preset['preset_id']);

			$pattern = $presets_manager->getPattern($preset['object_type']);
			$schema = $reader->getSchema();
			$schema->showNotifications();
			$schema = $schema->getData();

			$remapping_schema = $presets_importer->getEximSchema(
				$schema,
				$fields_mapping,
				$pattern
			);

			if ($remapping_schema) {
				$presets_importer->setPattern($pattern);
				$result = $reader->getContents(null, $schema);
				$result->showNotifications();

				$import_items = $presets_importer->prepareImportItems(
					$result->getData(),
					$fields_mapping,
					$preset['object_type'],
					true,
					$remapping_schema
				);

				$presets_manager->update($preset['preset_id'], array(
					'last_launch' => TIME,
					'last_status' => ImportStatuses::IN_PROGRESS,
				));

				$preset['options']['preset'] = $preset;
				unset($preset['options']['preset']['options']);
				ob_start();
				define('AJAX_REQUEST', true);
				$is_success = fn_import($pattern, $import_items, $preset['options']);
				ob_clean();
				if ($is_success) fn_echo('Success');
			}
		} catch (ReaderNotFoundException $e) {
			fn_echo(__('error_exim_cant_read_file'));
		} catch (PermissionsException $e) {
			fn_echo(__('advanced_import.cant_load_file_for_company'));
		} catch (FileNotFoundException $e) {
			fn_echo(__('advanced_import.cant_load_file_for_company'));
		}
	}
}