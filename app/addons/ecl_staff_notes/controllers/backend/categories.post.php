<?php
/*****************************************************************************
*                                                                            *
*                   All rights reserved! eCom Labs LLC                       *
* http://www.ecom-labs.com/about-us/ecom-labs-modules-license-agreement.html *
*                                                                            *
*****************************************************************************/

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'update') {
	if (fn_ecl_check_staff_notes_privilege('view_staff_notes') != false) {
		$category_id = empty($_REQUEST['category_id']) ? 0 : intval($_REQUEST['category_id']);
	
		$notes = db_get_field('SELECT staff_notes FROM ?:categories WHERE category_id = ?i', $category_id);
	
		Registry::get('view')->assign('staff_notes', $notes);
		Registry::get('view')->assign('show_staff_notes', true);
		Registry::get('view')->assign('hide_staff_form', fn_ecl_check_staff_notes_privilege('manage_staff_notes') == false ? 'Y' : 'N');
	}
}