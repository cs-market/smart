<?php
/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*      Copyright (c) 2013 CS-Market Ltd. All rights reserved.             *
*                                                                         *
*  This is commercial software, only users who have purchased a valid     *
*  license and accept to the terms of the License Agreement can install   *
*  and use this program.                                                  *
*                                                                         *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*  PLEASE READ THE FULL TEXT OF THE SOFTWARE LICENSE AGREEMENT IN THE     *
*  "license agreement.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.  *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * **/

use Tygh\Registry;
use Tygh\Settings;
use Tygh\Http;
use Tygh\Addons\SchemesManager;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$parent_directories = fn_get_parent_directory_stack(str_replace(Registry::get('config.dir.addons'), '', __FILE__), '\\/');
	$addon = end($parent_directories);
	$addon = trim($addon, '\\/');

	if ($mode=='update' && $_REQUEST['addon'] == $addon && ($action == 'activate' || (fn_get_addon_version($addon) == '0.0.1') )) {
		$request = array (
			'dispatch' => 'packages.check_upgrade',
			'domain' => Registry::get('config.http_host'),
			'license_key' => Settings::instance()->getValue('license_key', $addon),
			'cscart_version' => PRODUCT_VERSION,
			'addon_version' => fn_get_addon_version($addon),
		);

		$response = HTTP::get("https://cs-market.com/index.php", $request);
		
		if (empty($response)) {
			fn_set_notification('W', __('warning'), __('activation_fault'));
			return array(CONTROLLER_STATUS_OK);
		}

		$data = simplexml_load_string($response);
		if ((string) $data->message) { 
			if (!empty($request['license_key']))
			fn_set_notification('N', __('notice'), (string) $data->message );
		} else {
			$parsed_data = array(
				'file' => (string) $data->file,
				'name' => (string) $data->name,
				'description' => (string) $data->description,
				'from_version' => (string) $data->from_version,
				'to_version' => (string) $data->to_version,
				'timestamp' => TIME,
				'size' => (int) $data->size,
				'type' => 'addon',
			);
		}
		if (!empty( $parsed_data['file']) ) {
			$request['dispatch'] = 'packages.get_upgrade';
			$response = HTTP::get("https://cs-market.com/index.php", $request);
			$res = fn_put_contents(Registry::get('config.dir.files') . 'tmp.zip', $response);

			$extract_path = Registry::get('config.dir.cache_misc') . 'tmp/addon_pack/';
			$addon_pack['name'] = 'tmp.zip';
			$addon_pack['path'] = Registry::get('config.dir.files').$addon_pack['name'];

			// Re-create source folder
			fn_rm($extract_path);
			fn_mkdir($extract_path);

			fn_copy($addon_pack['path'], $extract_path . $addon_pack['name']);
			fn_rm($addon_pack['path']);
			if (fn_decompress_files($extract_path . $addon_pack['name'], $extract_path)) {
				fn_rm($extract_path . $addon_pack['name']);

				$struct = fn_get_dir_contents($extract_path, false, true, '', '', true);
				$addon_name = '';
				$relative_addon_path = str_replace(Registry::get('config.dir.root') . '/', '', Registry::get('config.dir.addons'));

				foreach ($struct as $file) {
					if (preg_match('#' . $relative_addon_path . '[^a-zA-Z0-9_]*([a-zA-Z0-9_-]+).+?addon.xml$#i', $file, $matches)) {
						if (!empty($matches[1])) {
							$addon_name = $matches[1];
						}
					}
				}

				if (empty($addon_name)) {
					fn_set_notification('E', __('error'), __('broken_addon_pack'));
				}

				$non_writable_folders = fn_check_copy_ability($extract_path, Registry::get('config.dir.root'));

				if (!empty($non_writable_folders)) {
					fn_set_notification('N', __('notice'), __('you_have_no_permissions') . ": " . $non_writable_folders);
					fn_redirect('addons.manage');
				} else {
					
					fn_copy($extract_path, Registry::get('config.dir.root'));
					fn_uninstall_addon($addon_name);
					SchemesManager::clearInternalCache($addon_name);
					fn_install_addon($addon_name);
					Settings::instance()->updateValue('license_key', $request['license_key'], $addon_name);
				}
			}
		}
		return array(CONTROLLER_STATUS_REDIRECT, 'addons.manage');
	}
}
