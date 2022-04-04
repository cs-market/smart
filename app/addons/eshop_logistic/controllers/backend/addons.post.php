<?php

use Tygh\Addons\EshopLogistic\Notifications\NotificationsHelper;
use Tygh\Registry;
use Tygh\Tygh;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'update') {

    if (!empty($_REQUEST['addon']) && $_REQUEST['addon'] == 'eshop_logistic') {

        $account_info = fn_eshop_logistic_get_account_info();
        
        Tygh::$app['view']->assign([
            'eshop_logistic_account_info' => $account_info
        ]);

        $use_maps = Registry::get('addons.eshop_logistic.eshop_use_maps');
        
        if ($use_maps == 'Y') {
            $geo_maps_status = Registry::get('addons.geo_maps.status');
            
            if ($geo_maps_status != 'A') {
                fn_set_notification('W', __('warning'), __("eshop_logistic.addon_not_enable"));
            }
        }
    }
}
