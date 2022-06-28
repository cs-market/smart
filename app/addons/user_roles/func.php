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

use Tygh\Enum\UserRoles;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_get_user_role_list() {
    return UserRoles::getList();
}

function fn_user_roles_get_users($params, $fields, $sortings, &$condition, $join, $auth) {
    if (!empty($params['user_role'])) {
        $condition['user_role'] = db_quote(' AND user_role = ?s', $params['user_role']);
    }
}
