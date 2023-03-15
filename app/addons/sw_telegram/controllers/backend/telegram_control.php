<?php

use Tygh\Registry;
use Tygh\Settings;
use Tygh\Http;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$params = $_REQUEST;

if ($mode == 'manage') {
    
    $params = array_merge(
        array('items_per_page' => Registry::get('settings.Appearance.admin_elements_per_page')),
        $_REQUEST
    );

    list( $data_list, $search ) = fn_sw_telegram__get_data_list( $params, Registry::get( 'settings.Appearance.admin_elements_per_page' ) );
    
    Tygh::$app['view']->assign( 'data_list', $data_list );
    Tygh::$app['view']->assign( 'search', $search );
    
    return array(CONTROLLER_STATUS_OK);
}