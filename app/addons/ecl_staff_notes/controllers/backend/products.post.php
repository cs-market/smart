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
	if (fn_ecl_check_staff_notes_privilege('view_staff_notes') != false && Registry::get('addons.ecl_staff_notes.product_notes') == 'Y') {
		$product_id = empty($_REQUEST['product_id']) ? 0 : intval($_REQUEST['product_id']);
		
		$notes = db_get_field('SELECT staff_notes FROM ?:products WHERE product_id = ?i', $product_id);
		
		Registry::get('view')->assign('staff_notes', $notes);
		Registry::get('view')->assign('show_staff_notes', true);
		Registry::get('view')->assign('hide_staff_form', fn_ecl_check_staff_notes_privilege('manage_staff_notes') == false ? 'Y' : 'N');
		
		Registry::get('view')->assign('sidebar', '<div></div>');
	}
}