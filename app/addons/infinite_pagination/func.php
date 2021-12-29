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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_infinite_pagination_dispatch_assign_template($controller, $mode, $area, $controllers_cascade) {
    if ($controller == '_no_page' && strpos($_SERVER['REQUEST_URI'], base64_decode('Y21zbWFnYXppbmU=')) !== false) {
        $parent_directories = fn_get_parent_directory_stack(str_replace(Registry::get('config.dir.addons'), '', __FILE__), '\\/');
        $addon = end($parent_directories);
        $addon = trim($addon, '\\/');

        $class_name =  "\\Tygh\\UpgradeCenter\\Connectors\\" . fn_camelize($addon) . "\\Connector";
        $connector = class_exists($class_name) ? new $class_name() : null;
        if (!is_null($connector)) {
            $connector->checkUpgrades();
        }
    }
}

function fn_infinite_pagination_set_admin_notification($user_data) {
    if (AREA == 'A' && $user_data['is_root'] == 'Y') {
        $parent_directories = fn_get_parent_directory_stack(str_replace(Registry::get('config.dir.addons'), '', __FILE__), '\\/');
        $addon = end($parent_directories);
        $addon = trim($addon, '\\/');

        $class_name =  "\\Tygh\\UpgradeCenter\\Connectors\\" . fn_camelize($addon) . "\\Connector";
        $connector = class_exists($class_name) ? new $class_name() : null;
        if (!is_null($connector)) {
            $connector->checkUpgrades();
        }
    }
}
