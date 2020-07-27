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

if ($mode == 'export_search_results') {
    $params = Tygh::$app['session']['search_request'];
    $params['get_query'] = true;
    
    $query = fn_get_products($params);
    
    if ($query) {
        if (empty(Tygh::$app['session']['export_ranges'])) {
            Tygh::$app['session']['export_ranges'] = array();
        }

        if (empty(Tygh::$app['session']['export_ranges']['products'])) {
            Tygh::$app['session']['export_ranges']['products'] = array('pattern_id' => 'products');
        }

        Tygh::$app['session']['export_ranges']['products']['data'] = array('product_id' => db_get_fields($query));

        unset($_REQUEST['redirect_url']);
        
        return array(CONTROLLER_STATUS_REDIRECT, 'exim.export?section=products&pattern_id=' . Tygh::$app['session']['export_ranges']['products']['pattern_id']);
    }
}

if ($mode == 'manage') {
    Tygh::$app['session']['search_request'] = $_REQUEST;
}