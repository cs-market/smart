<?php 

use Tygh\Registry;
use Tygh\Tygh;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$_cart = &Tygh::$app['session']['cart'];

if ($mode == 'update') {

    if (!defined("AJAX_REQUEST")) {
        
    
        if (!empty($_cart['order_id'])) {
            $old_ship_data = db_get_field('SELECT data FROM ?:order_data WHERE order_id = ?i AND type = ?s', $_cart['order_id'], 'L');

            if (!empty($old_ship_data)) {
                $old_ship_data = unserialize($old_ship_data);
                
                foreach($old_ship_data as $group_key => $shipping) {
                    if ($shipping['module'] == 'eshop_logistic' && !empty($shipping['office_id'])) {
                        $_cart['select_office'][$shipping['group_key']][$shipping['shipping_id']] = $shipping['office_id'];
                        break;
                    }
                }
            }
        }    
    }
    
}
if ($mode == 'update_payment') {

    $payment_info = fn_get_payment_method_data($_REQUEST['payment_id']);
        
    if (!empty($payment_info['eshop_payment_type'])) {
        $_cart['payment_method_data']['eshop_changed_payment'] = $payment_info['eshop_payment_type'];
        $_cart['calculate_shipping'] = true;
    }
}