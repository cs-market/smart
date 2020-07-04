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


Tygh::$app['session']['cart'] = isset(Tygh::$app['session']['cart']) ? Tygh::$app['session']['cart'] : array();
$cart = & Tygh::$app['session']['cart'];

if ( !defined('BOOTSTRAP') ) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'send_custom_sms') {
        if ($_REQUEST['custom_sms_content']){           
            fn_custom_sms_send($cart['order_id'], $_REQUEST['custom_sms_content']);
        }
    }
}

