<?php

use Tygh\Registry;
use Tygh\Tygh;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST' 
    || defined('CONSOLE')
    || Registry::get('runtime.controller') == 'exim_1c'
    ) {

        return ;
}

$auth = Tygh::$app['session']['auth'];
$cart = & Tygh::$app['session']['cart'];
$session = & Tygh::$app['session'];
$last_clear_cache_time = fn_eshop_logistic_get_last_clear_cache_time();

if (empty($session['eshop_last_clear_cache_time']) || $session['eshop_last_clear_cache_time'] < $last_clear_cache_time) {

    fn_eshop_logistic_delete_session_data();
    fn_delete_session_data('eshop_services_info');
    $cart['calculate_shipping'] = true;
    $session['eshop_last_clear_cache_time'] = time();
}