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

namespace Tygh\UpgradeCenter\Connectors\DecimalAmount;

use Tygh\Registry;
use Tygh\Http;
use Tygh\UpgradeCenter\Connectors\IConnector as UCInterface;

/**
 * Core upgrade connector interface
 */
class Connector implements UCInterface
{
	/**
	 * Upgrade server URL
	 *
	 * @var string $updates_server
	 */
	protected $updates_server = '';

	/**
	 * Upgrade center settings
	 *
	 * @var array $uc_settings
	 */
	protected $uc_settings = array();

	/**
	 * Prepares request data for request to Upgrade server (Check for the new upgrades)
	 *
	 * @return array Prepared request information
	 */
	public function getConnectionData()
	{
		$request_data = array(
			'method' => 'get',
			'url' => $this->updates_server,
			'data' => array(
				'dispatch' => 'packages.check_upgrade',
				'cscart_version' => PRODUCT_VERSION,
				'domain' => Registry::get('config.http_host'),
				'license_key' => $this->uc_settings['license_key'],
				'addon_version' => $this->uc_settings['addon_version'],
				'addon' => $this->uc_settings['addon'],
			),
			'headers' => array(
				'Content-type: text/xml'
			)
		);
		return $request_data;
	}

	/**
	 * Processes the response from the Upgrade server.
	 *
	 * @param  string $response			server response
	 * @param  bool   $show_upgrade_notice internal flag, that allows/disallows Connector displays upgrade notice (A new version of [product] available)
	 * @return array  Upgrade package information or empty array if upgrade is not available
	 */
	public function processServerResponse($response, $show_upgrade_notice)
	{
		$parsed_data = array();
		$data = simplexml_load_string($response);
		if ((string) $data->message) {
			if (!empty($this->uc_settings['license_key']))
			fn_set_notification('N', __('notice'), (string) $data->message );
		} elseif (!$data) {
			return;
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

		return $parsed_data;
	}

	/**
	 * Downloads upgrade package from the Upgade server
	 *
	 * @param  array  $schema	   Package schema
	 * @param  string $package_path Path where the upgrade pack must be saved
	 * @return bool   True if upgrade package was successfully downloaded, false otherwise
	 */
	public function downloadPackage($schema, $package_path)
	{
		$request = array (
			'dispatch' => 'packages.get_upgrade',
			'domain' => Registry::get('config.http_host'),
			'license_key' => $this->uc_settings['license_key'],
			'addon_version' => $this->uc_settings['addon_version'],
			'cscart_version' => PRODUCT_VERSION,
		);

		$data = HTTP::get($this->updates_server, $request);
		if (!empty($data)) {
		$result = array(true, '');
			fn_put_contents($package_path, $data);
		} else {
			$result = array(false, __('text_uc_cant_download_package'));
		}

		return $result;
	}

	public function __construct()
	{
		$parent_directories = fn_get_parent_directory_stack(str_replace(Registry::get('config.dir.addons'), '', __FILE__), '\\/');
		$addon = end($parent_directories);
		$addon = trim($addon, '\\/');

		$this->updates_server = 'https://cs-market.com/index.php';
		$this->uc_settings = Registry::get("addons.$addon");
		$this->uc_settings['addon_version'] = fn_get_addon_version($addon);
		$this->uc_settings['addon'] = $addon;
	}
}
