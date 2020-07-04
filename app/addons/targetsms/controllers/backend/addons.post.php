<?php
/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*      Copyright (c) 2015-2017 LLC Alt-team. All rights reserved.           *
*                                                                           *
*  This is commercial software, only users who have purchased a valid       *
*  license and accept to the terms of the License Agreement can install     *
*  and use this program.                                                    *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*  PLEASE READ THE FULL TEXT OF THE SOFTWARE LICENSE AGREEMENT IN THE       *
*  "pd_copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.         *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

use Tygh\Registry;

if ( !defined('BOOTSTRAP') ) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'update') {
    	$sender_data = $_REQUEST['sender_data'];

    	foreach ($sender_data as $company_id => $company) {
    		db_query("UPDATE ?:companies SET sms_sender_name = ?s WHERE company_id = ?i", $company['sms_sender_name'], $company_id);
    	}
    }
}

if ($mode == 'send_sms') {
    if ($_REQUEST['is_ajax']){
    	$phones = explode(",", $_REQUEST['phones']);

    	foreach ($phones as $key => $phone) {
    		fn_targetsms_send_sms($phone,$_REQUEST['text'],$_REQUEST['senders']);
    	}

    	return false;
    }
}