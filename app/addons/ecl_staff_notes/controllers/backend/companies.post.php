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
    if ($mode == 'update') {
        fn_update_staff_notes($_REQUEST, $auth);
    }

    return;
}

if ($mode == 'update') {
    if (fn_ecl_check_staff_notes_privilege('view_staff_notes') != false && fn_allowed_for('MULTIVENDOR') && ACCOUNT_TYPE != 'vendor') {
        $company_id = empty($_REQUEST['company_id']) ? 0 : intval($_REQUEST['company_id']);
        
        $notes = db_get_field('SELECT staff_notes FROM ?:companies WHERE company_id = ?i', $company_id);

        Registry::get('view')->assign('staff_notes', $notes);
        Registry::get('view')->assign('show_staff_notes', true);
        Registry::get('view')->assign('hide_staff_form', fn_ecl_check_staff_notes_privilege('manage_staff_notes') == false ? 'Y' : 'N');
        
        if (!Registry::get('runtime.company_id')) {
            $navigation = Registry::get('navigation');
            $navigation['dynamic']['sections'][] = array(
                'type' => 'divider'
            );
            Registry::set('navigation', $navigation);
        }
    }
}