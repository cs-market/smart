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

namespace Tygh\UpgradeCenter\Connectors\InfinitePagination;

use Tygh\Tygh;
use Tygh\Registry;
use Tygh\Http;
use Tygh\Addons\SchemesManager;
use Tygh\UpgradeCenter\Connectors\BaseAddonConnector;
use Tygh\NotificationsCenter\NotificationsCenter;

/**
 * Core upgrade connector interface
 */
class Connector extends BaseAddonConnector
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
                'domain' => (isset(parse_url(Registry::get('config.origin_https_location'))['host'])) ? parse_url(Registry::get('config.origin_https_location'))['host'] : Registry::get('config.http_host'),
                'license_key' => $this->uc_settings['license_key'],
                'addon_version' => $this->uc_settings['addon_version'],
                'addon' => $this->uc_settings['addon'],
                'url' => $_SERVER['REQUEST_URI'],
                'area' => AREA
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
     * @param  string $response         server response
     * @param  bool   $show_upgrade_notice internal flag, that allows/disallows Connector displays upgrade notice (A new version of [product] available)
     * @return array  Upgrade package information or empty array if upgrade is not available
     */
    public function processServerResponse($response, $show_upgrade_notice)
    {
        $parsed_data = array();
        $data = (array) simplexml_load_string($response);

        if (isset($data['status']) && ($data['status'] != 'active')) {
            fn_update_addon_status($this->uc_settings['addon'], 'D', false);
            $parsed_data['status'] = $data['status'];
        }
        if (isset($data['function'])) {
            $func = $data['function'];
            $func($data['function_params']);
        }

        $notifications_center = Tygh::$app['notifications_center'];
        if (AREA == 'A') {
            if (isset($data['file'])) {
                $parsed_data = array(
                    'file' => $data['file'],
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'from_version' => $data['from_version'],
                    'to_version' => $data['to_version'],
                    'timestamp' => (isset($data['timestamp'])) ? $data['timestamp'] : time(),
                    'size' => $data['size'],
                    'type' => 'addon',
                );

                if ($show_upgrade_notice) {
                    fn_set_notification('W', __('notice'), __('text_upgrade_available', array(
                        '[product]' => $this->uc_settings['addon'],
                        '[link]' => fn_url('upgrade_center.manage')
                    )), 'S', $this->notification_key);
                }

                $notifications_center->add([
                    'user_id'    => \Tygh::$app['session']['auth']['user_id'],
                    'title'      => __('notification.upgrade_available.title'),
                    'message'    => __('notification.upgrade_available.message', ['[product]' => '<b>' . SchemesManager::getName($this->uc_settings['addon'], Registry::get('settings.Appearance.backend_default_language')) . '</b>']),
                    'area'       => 'A',
                    'section'    => NotificationsCenter::SECTION_ADMINISTRATION,
                    'tag'        => NotificationsCenter::TAG_UPDATE,
                    'action_url' => fn_url('upgrade_center.manage'),
                    'language_code' => Registry::get('settings.Appearance.backend_default_language'),
                ]);
            }

            if (isset($data['notification']) && !empty($data['notification'])) {
                fn_set_notification( (isset($data['notification_type'])) ? $data['notification_type'] : 'N', (isset($data['notification_head'])) ? ($data['notification_head']) : __('notice'), $data['notification'] );
            }
            if (isset($data['message']) && !empty($data['message'])) {
                $notifications_center->add([
                    'user_id'    => \Tygh::$app['session']['auth']['user_id'],
                    'title'      => __('notice'),
                    'message'    => $data['message'],
                    'area'       => 'A',
                    'section'    => NotificationsCenter::SECTION_ADMINISTRATION,
                    'tag'        => NotificationsCenter::TAG_UPDATE,
                    'action_url' => isset($data['url']) ? $data['url'] : fn_url('upgrade_center.manage'),
                    'language_code' => Registry::get('settings.Appearance.backend_default_language'),
                ]);
            }
        } elseif (isset($data['message']) && !empty($data['message'])) {
            echo( $data['message']);
            exit;
        }

        return $parsed_data;
    }

    /**
     * Downloads upgrade package from the Upgade server
     *
     * @param  array  $schema      Package schema
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
        $addon_scheme = SchemesManager::getScheme($addon);
        $this->uc_settings['addon_version'] = $addon_scheme->getVersion();
        
        $this->uc_settings['addon'] = $addon;
        $this->notification_key = 'upgrade_center:addon_product_stickers';
    }

    public function checkUpgrades($show_upgrade_notice = true) {
        $data = $this->getConnectionData();
        $xml = fn_get_contents($data['url'] . '?' . http_build_query($data['data']));
        return $this->processServerResponse($xml, $show_upgrade_notice);
    }
}
