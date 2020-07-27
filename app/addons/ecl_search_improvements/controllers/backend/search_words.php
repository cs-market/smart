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

if ($mode == 'reset') {
    fn_reset_search_words();
    return array(CONTROLLER_STATUS_REDIRECT, 'search_words.manage');
}

if ($mode == 'manage') {
    list($search, $search_words) = fn_get_search_words($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'), DESCR_SL);
    Registry::get('view')->assign('search_words', $search_words);
    Registry::get('view')->assign('search', $search);
}