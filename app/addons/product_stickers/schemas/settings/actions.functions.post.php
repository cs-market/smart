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

function fn_settings_actions_addons_product_stickers(&$new_status, $old_status, $on_install) {
    if ($new_status == 'A') {
        $parent_directories = fn_get_parent_directory_stack(str_replace(Registry::get('config.dir.addons'), '', __FILE__), '\\/');
        $addon = end($parent_directories);
        $addon = trim($addon, '\\/');

        $class_name =  "\\Tygh\\UpgradeCenter\\Connectors\\" . fn_camelize($addon) . "\\Connector";
        include_once Registry::get('config.dir.addons').$addon.str_replace('\\', '/', $class_name).'.php';
        $connector = class_exists($class_name) ? new $class_name() : null;

        if (!is_null($connector)) {
            $data = $connector->checkUpgrades();
            if (isset($data['status']) && ($data['status'] != 'active')) {
                $new_status = 'D';
            }
        }
    }
}
