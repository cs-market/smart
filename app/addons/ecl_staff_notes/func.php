<?php
/*****************************************************************************
*                                                                            *
*                   All rights reserved! eCom Labs LLC                       *
* http://www.ecom-labs.com/about-us/ecom-labs-modules-license-agreement.html *
*                                                                            *
*****************************************************************************/

if (!defined('BOOTSTRAP')) { die('Access denied'); }

// sidebar is displayed under common form for users
function fn_update_staff_notes($params, $auth = array())
{
	
	$objects = array(
		'products' => 'product_id',
		'categories' => 'category_id',
		'pages' => 'page_id',
		'users' => 'user_id',
		'news' => 'news_id',
		'companies' => 'company_id'
	);

	if (fn_ecl_check_staff_notes_privilege('manage_staff_notes') != true || !isset($params['staff_notes'])) {
		return false;
	}
	
	db_query("UPDATE ?:" . $params['type'] . " SET staff_notes = ?s WHERE " . $objects[$params['type']] . " = ?i", $params['staff_notes'], $params['object_id']);

	return true;
}

function fn_ecl_check_staff_notes_privilege($priv = '')
{
	if (ACCOUNT_TYPE != 'admin' && ACCOUNT_TYPE != 'vendor') {
		return false;
	}
	if ($_SESSION['auth']['is_root'] != 'Y' && !empty($_SESSION['auth']['usergroup_ids'])) {
       $allowed = db_get_fields("SELECT privilege FROM ?:usergroup_privileges WHERE usergroup_id IN(?n)", $_SESSION['auth']['usergroup_ids']);

        if (!in_array($priv, $allowed)) {
            return false;
        }
    }

    return true;
}