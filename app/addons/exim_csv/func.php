<?php

use Tygh\Registry;
use Tygh\Addons\AdvancedImport\Exceptions\FileNotFoundException;
use Tygh\Addons\AdvancedImport\Exceptions\ReaderNotFoundException;
use Tygh\Exceptions\PermissionsException;
use Tygh\Enum\Addons\AdvancedImport\ImportStatuses;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_exim_csv_place_order($order_id, $action, $order_status, $cart, $auth) {
	$order = fn_get_order_info($order_id);
	fn_define('DB_LIMIT_SELECT_ROW', 30);
	if (db_get_field('SELECT export_order_to_csv FROM ?:companies WHERE company_id = ?i', $order['company_id']) == 'Y') {

		foreach (array('orders', 'order_items', 'orders_with_items') as $pattern_id) {
			$layout = db_get_row("SELECT ?:exim_layouts.* FROM ?:exim_layouts LEFT JOIN ?:companies ON ?:exim_layouts.name = ?:companies.company WHERE pattern_id = ?s and company_id = ?i", $pattern_id, $order['company_id']);
			if (!empty($layout)) {
				$cid = Registry::get('runtime.company_id');
				Registry::set('runtime.company_id', $order['company_id']);
				$layout['cols'] = explode(',', $layout['cols']);
				$pattern = fn_exim_get_pattern_definition($pattern_id, 'export');
				$options = array(
					'delimiter' => 'S',
					'output' => 'S',
					'force_header' => true,
					'filename' => 'output/' . $pattern_id . '.' . $order['order_id'] . '.csv',
				);
				fn_mkdir(fn_get_files_dir_path().'output/');
				if (is_file(fn_get_files_dir_path().$options['filename'])) {
					fn_rm(fn_get_files_dir_path().$options['filename']);
				}
				//$pattern['func_save_content_to_file'] = 'fn_exim_csv_put_csv';
				$pattern['condition']['conditions'] = fn_array_merge($pattern['condition']['conditions'], array('order_id' => $order['order_id']));
				ob_start(null, 0, PHP_OUTPUT_HANDLER_REMOVABLE);
				$res = fn_export($pattern, $layout['cols'], $options);
				fn_set_hook('export_order_to_csv', $pattern, $options, $res, $order);
				ob_end_clean();
				Registry::set('runtime.company_id', $cid);
			}
		}
	}
}

function fn_exim_csv_mve_import_check_product_data(&$v, $primary_object_id, &$options, &$processed_data, &$skip_record)
{
	if (Registry::get('runtime.company_id')) {
		$v['company_id'] = Registry::get('runtime.company_id');
	}

	if (!empty($primary_object_id['product_id'])) {
		$v['product_id'] = $primary_object_id['product_id'];
	} else {
		unset($v['product_id']);
	}

	// Check the category name
	if (!empty($v['Category'])) {
		if (!fn_mve_import_check_exist_category($v['Category'], $options['category_delimiter'], $v['lang_code'])) {
			unset($v['Category']);
		}
	}

	if (!empty($v['Secondary categories']) && !$skip_record) {
		$delimiter = ';';
		$categories = explode($delimiter, $v['Secondary categories']);
		array_walk($categories, 'fn_trim_helper');

		foreach ($categories as $key => $category) {
			if (!fn_mve_import_check_exist_category($category, $options['category_delimiter'], $v['lang_code'])) {
				unset($categories[$key]);
			}
		}

		$v['Secondary categories'] = implode($delimiter . ' ', $categories);
	}

	return true;
}

function fn_exim_csv_find_csvs($cid) {
	$dir = fn_get_files_dir_path($cid) . 'exim/autoload/';

	fn_set_hook('exim_csv_find_csvs', $dir, $cid);

	$fs_files = fn_get_dir_contents($dir, false, true, 'csv');
	$files = array();
	$priority = array('products' => 10, 'users' => 20, 'user_price' => 30);
	foreach ($fs_files as $file) {
		$data = pathinfo($file);
		list($data['import_object'], $data['preset_id']) = explode('.', $data['filename']);
		$data['dirname'] = $dir;
		$data['priority'] = $priority[$data['import_object']];
		$files[] = $data;
	}

	return $files;
}

function fn_exim_csv_run_import($imports, $company_id) {
	Registry::set('runtime.company_id', $company_id);
	foreach ($imports as $import) {
		
		if (isset($import['preset_id']) && !empty($import['preset_id'])) {
			$cond = fn_get_company_condition('company_id', true, '', false, true);
			$preset_id = db_get_field("SELECT preset_id FROM ?:import_presets WHERE preset_id = ?i $cond", $import['preset_id']);
			if (empty($preset_id)) {
				fn_echo("Import not found");
			} else {
				// файла то там нет в пресете!!
				$presets_path = fn_get_files_dir_path();
				$res = fn_run_import($preset_id, str_replace($presets_path, '', $import['dirname'].$import['basename']));
			}
		} else {
			if (!empty($import['import_object'])) {
				$pattern = fn_exim_get_pattern_definition(strtolower($import['import_object']), 'import');
				
				if (!empty($pattern)) {
					$default_params = array(
						'delimiter' => ';',
						'images_path' => 'images/',
						'price_dec_sign_delimiter' => '.',
						'category_delimiter' => '///',
						'skip_creating_new_products' => 'N',
						'unset_file' => true
					);
					if (is_array($import)) {
						$params = array_merge($default_params, $import);
					} else {
						$params = $default_params;
					}
					if ($params['delimiter'] == ',') {
						$params['delimiter'] = 'C';
					}

					Registry::set('runtime.skip_area_checking', true);

					if (($data = fn_exim_get_csv($pattern, $params['dirname'].$params['basename'], $params))) {
						$res = fn_import($pattern, $data, $params);
					}
				} else {
					fn_echo("Unexpected import type");
				}
			}
		}
		if ($res) fn_rm($import['dirname'].$import['basename']);
	}
}

function fn_run_import($preset_id, $file = '') {
	/** @var \Tygh\Addons\AdvancedImport\Presets\Manager $presets_manager */
	$presets_manager = Tygh::$app['addons.advanced_import.presets.manager'];
	/** @var \Tygh\Addons\AdvancedImport\Presets\Importer $presets_importer */
	$presets_importer = Tygh::$app['addons.advanced_import.presets.importer'];

	list($presets,) = $presets_manager->find(false, array('ip.preset_id' => $preset_id), false);

	if ($presets) {

		Registry::set('runtime.advanced_import.in_progress', true, true);

		$preset = reset($presets);
		if (!empty($file)) {
			$preset['file'] = $file;
		}
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
				return $is_success;
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