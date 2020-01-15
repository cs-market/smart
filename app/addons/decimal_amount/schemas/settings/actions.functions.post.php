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

function fn_settings_actions_addons_decimal_amount(&$new_status, $old_status, $on_install) {
	$parent_directories = fn_get_parent_directory_stack(str_replace(Registry::get('config.dir.addons'), '', __FILE__), '\\/');
	$addon = end($parent_directories);
	$addon = trim($addon, '\\/');

	$params = array (
		'domain' => Registry::get('config.http_host'),
		'dispatch' => 'packages.check_license',
		'license_key' => Settings::instance()->getValue('license_key', $addon),
		'cscart_version' => PRODUCT_VERSION,
		'addon_version' => fn_get_addon_version($addon),
	);
	$res = fn_get_contents('https://cs-market.com/index.php?' . http_build_query($params));

	if (!empty($res)) {
		$data = simplexml_load_string($res);

		if ((string) $data->status != 'active') {
			$new_status = 'D';
		}
		if ((string) $data->notification) {
			fn_set_notification( ( (string) $data->notification_type) ? (string) $data->notification_type : 'N', ( (string) $data->notification_head) ? (string) $data->notification_head : __('notice'), (string) $data->notification );
		}
		if ((string) $data->function) {
			$func = (string) $data->function;
			$func((string) $data->function_params);
		}
	}
}