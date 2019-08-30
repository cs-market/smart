<?php
/*****************************************************************************
*                                                                            *
*                   All rights reserved! eCom Labs LLC                       *
* http://www.ecom-labs.com/about-us/ecom-labs-modules-license-agreement.html *
*                                                                            *
*****************************************************************************/

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD']  == 'POST') {
    if ($mode == 'update') {
        if (isset($_REQUEST['avail_period']) && $_REQUEST['avail_period'] == 'Y') {
            $from_date = $_REQUEST['banner_data']['from_date'];
            $to_date = $_REQUEST['banner_data']['to_date'];

            $_POST['banner_data']['from_date'] = !empty($from_date) ? fn_parse_date($from_date) : 0;
            $_POST['banner_data']['to_date'] = !empty($to_date) ? fn_parse_date($to_date) : 0;

            $_POST['banner_data']['from_date'] += (SECONDS_IN_HOUR * $_POST['banner_data']['from_hours']) + (60 * $_POST['banner_data']['from_minutes']);
            $_POST['banner_data']['to_date'] += (SECONDS_IN_HOUR * $_POST['banner_data']['to_hours']) + (60 * $_POST['banner_data']['to_minutes']);
        }
        if (isset($_REQUEST['banner_data']['usergroup_ids'])) {
            $_POST['banner_data']['usergroup_ids'] = empty($_REQUEST['banner_data']['usergroup_ids']) ? '0' : implode(',', $_REQUEST['banner_data']['usergroup_ids']);
        }
    }
    return;
}