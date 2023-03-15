<?php

namespace Tygh\UpgradeCenter\Connectors\SwTelegram;

use Tygh\Addons\SchemesManager;
use Tygh\Http;
use Tygh\Registry;
use Tygh\Settings;
use Tygh\Tools\Url;
use Tygh\UpgradeCenter\Connectors\BaseAddonConnector;
use Tygh\UpgradeCenter\Connectors\IConnector;

class Connector extends BaseAddonConnector implements IConnector
{
    const ACTION_PARAM = 'dispatch';
    const ACTION_CHECK_UPDATES = 'license_check_2.update';
    const ACTION_DOWNLOAD_PACKAGE = 'license_check_2.update';
    protected $addon_id = 'sw_telegram';
    protected $addon_version;
    protected $product_url;

    public function __construct()
    {
        parent::__construct();
        $this->updates_server = SWEET_SERVER;

        $addon = SchemesManager::getScheme($this->addon_id);
        $this->addon_version = $addon->getVersion() ? $addon->getVersion() : '1.0';

        $this->product_name = PRODUCT_NAME;
        $this->product_version = PRODUCT_VERSION;
        $this->product_build = PRODUCT_BUILD;
        $this->product_edition = PRODUCT_EDITION;
        $this->product_url = Registry::get('config.current_location');
		$this->domain_url = Registry::get('config.http_host');
    }

    public function getConnectionData()
    {
        $data = [
            self::ACTION_PARAM => self::ACTION_CHECK_UPDATES,
            'addon_id'         => $this->addon_id,
            'addon_version'    => $this->addon_version,
            'product_name'     => $this->product_name,
            'product_version'  => $this->product_version,
            'product_build'    => $this->product_build,
            'product_edition'  => $this->product_edition,
            'product_url'      => $this->product_url,
			'domain'		   => $this->domain_url,
			'lang_code'		   => DESCR_SL,
        ];
        
        $headers = [];
		
        return [
            'method'  => 'get',
            'url'     => $this->updates_server,
            'data'    => $data,
            'headers' => $headers,
        ];
    }

    public function downloadPackage($schema, $package_path)
    {
        $download_url = new Url($this->updates_server);

        $download_url->setQueryParams(array_merge($download_url->getQueryParams(), [
            self::ACTION_PARAM => self::ACTION_DOWNLOAD_PACKAGE,
			'get_file'         => true,
            'package_id'       => $schema['package_id'],
			'addon_id'         => $this->addon_id,
			'addon_version'    => $this->addon_version,
            'addon_id'         => $this->addon_id,
			'product_edition'  => $this->product_edition,
			'product_url'      => $this->product_url,
			'domain'		   => $this->domain_url,
			'lang_code'		   => DESCR_SL,
			'product_build'		   => $this->product_build
        ]));

        $download_url = $download_url->build();
		
		$request_result_pre = Http::get($download_url);
		
		if (!$request_result_pre || $request_result_pre == false) {
            $download_result = [false, __('text_uc_cant_download_package')];
            fn_rm($package_path);
			return $download_result;
        }

        $request_result = Http::get($download_url, [], [
            'write_to_file' => $package_path,
        ]);
		
		$request_result_pre = Http::get($download_url);

        if (!$request_result || strlen($error = Http::getError())) {
            $download_result = [false, __('text_uc_cant_download_package')];

            fn_rm($package_path);
        } else {
            $download_result = [true, ''];
        }

        return $download_result;
    }
}
