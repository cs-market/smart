<?php

use Tygh\Addons\EshopLogistic\Requests\CitiesCodes;
use Tygh\Addons\EshopLogistic\Requests\Site;
use Tygh\Registry;
use Tygh\Tygh;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $return_controller = 'eshop_logistic.logs';
    $return_status = CONTROLLER_STATUS_OK;

    if ($mode == 'get_account_data') {

        $site = new Site();

        if (is_object($site)) {
            $site->request();
            fn_eshop_logistic_clear_cache();
        }
        
        $return_controller = 'addons.update&addon=eshop_logistic';
        $return_status = CONTROLLER_STATUS_REDIRECT;

    }elseif ($mode == 'clear_logs') {

        fn_eshop_logistic_clear_logs();
        

    }elseif ($mode == 'clear_old_logs') {

        fn_eshop_logistic_clear_old_logs();
    }

    return [$return_status, $return_controller];
}

if ($mode == 'logs') {

    $params = $_REQUEST;
    list($logs, $search) = fn_eshop_logistic_get_logs($params, Registry::get('settings.Appearance.admin_elements_per_page'), DESCR_SL);

    Tygh::$app['view']->assign([
        'logs'      => $logs,
        'search'    => $search 
    ]);
}elseif ($mode == 'clear_cache') {


    fn_eshop_logistic_clear_cache();
    
    if (empty($_REQUEST['redirect_url'])) {
        $_REQUEST['redirect_url'] = 'index.index';
    }

    return array(CONTROLLER_STATUS_REDIRECT);

}elseif ($mode == 'cron_clear_logs') {

    $cron_pass = Registry::get('addons.eshop_logistic.cron_pass');
    
    if (!empty($_REQUEST['cron_pass']) && $cron_pass == $_REQUEST['cron_pass']) { 

        fn_eshop_logistic_clear_logs();

    }else {
        die('access denided');
    }

    die('done');

}elseif ($mode == 'get_cities_codes') {
    
    $cities_codes = new CitiesCodes();
    
    if (is_object($cities_codes)) {

        $page = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 0;

        $cities_codes->request($page);
    }

    fn_set_notification('N', __('notice'), __('successful'));
    fn_redirect('addons.update&addon=eshop_logistic&selected_section=settings');
}  
 
