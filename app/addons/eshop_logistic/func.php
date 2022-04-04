<?php

use Tygh\Addons\EshopLogistic\Requests\Info;
use Tygh\Addons\EshopLogistic\Requests\Site;
use Tygh\Enum\Addons\EshopLogistic\EshopEnum;
use Tygh\Enum\Addons\EshopLogistic\LoggerEnum;
use Tygh\Registry;
use Tygh\Template\Document\Variables\PickpupPointVariable;
use Tygh\Tygh;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/** HOOKS */

function fn_eshop_logistic_pickup_point_variable_init(
    PickpupPointVariable $instance,
    $order,
    $lang_code,
    &$is_selected,
    &$name,
    &$phone,
    &$full_address,
    &$open_hours_raw,
    &$open_hours,
    &$description_raw,
    &$description
) {
    if (!empty($order['shipping'])) {
        if (is_array($order['shipping'])) {
            $shipping = reset($order['shipping']);
        } else {
            $shipping = $order['shipping'];
        }

        
        if (!isset($shipping['module']) || $shipping['module'] !== 'eshop_logistic') {
            return;
        }

        $select_office = !empty($shipping['office_id']) ? $shipping['office_id'] : false;

        if (!$select_office) {
            return;
        }
        
        if (isset($shipping['data']['terminals'])) {

            foreach($shipping['data']['terminals'] as $group_key => $eshop_teminal) {
                
                if ($eshop_teminal['code'] == $select_office) {
                    $pickup_data = $eshop_teminal;
                    break;    
                }
                
            }

            if (empty($pickup_data)) {
                return;
            }

            $is_selected = true;
            $name = $pickup_data['code'];
            $full_address = $pickup_data['address'];
        }
    }
    
    return;
}
function fn_eshop_logistic_calculate_cart_taxes_pre(&$cart, $cart_products, &$product_groups)
{

    if (!empty($cart['shippings_extra']['data']['eshop'])) {
        
        if (!empty($_REQUEST['eshop_service_terminal'])) {
            $select_office = $cart['select_office'] = $_REQUEST['eshop_service_terminal'];
        }
        elseif (!empty($cart['select_office'])) {
            $select_office = $cart['select_office'];

        }
        
        if (!empty($select_office)) {
            foreach ($product_groups as $group_key => $group) {
                if (!empty($group['chosen_shippings'])) {
                    foreach ($group['chosen_shippings'] as $shipping_key => $shipping) {
                        $shipping_id = $shipping['shipping_id'];

                        if($shipping['module'] != 'eshop_logistic') {
                            continue;
                        }
                        
                        if (!empty($cart['shippings_extra']['data']['eshop'][$group_key][$shipping_id])) {
                            $shippings_extra = $cart['shippings_extra']['data']['eshop'][$group_key][$shipping_id];
                            $product_groups[$group_key]['chosen_shippings'][$shipping_key]['data'] = $shippings_extra;
                            if (!empty($select_office[$group_key][$shipping_id])) {
                                $office_id = $select_office[$group_key][$shipping_id];
                                $product_groups[$group_key]['chosen_shippings'][$shipping_key]['office_id'] = $office_id;
                                
                                if (!empty($shippings_extra['offices'][$office_id])) {
                                    $office_data = $shippings_extra['offices'][$office_id];
                                    $product_groups[$group_key]['chosen_shippings'][$shipping_key]['office_data'] = $office_data;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($cart['shippings_extra']['data']['eshop'])) {
            foreach ($cart['shippings_extra']['data']['eshop'] as $group_key => $shippings) {
                foreach ($shippings as $shipping_id => $shippings_extra) {
                    if (!empty($product_groups[$group_key]['shippings'][$shipping_id]['module'])) {
                        $module = $product_groups[$group_key]['shippings'][$shipping_id]['module'];

                        if ($module == 'eshop_logistic' && !empty($shippings_extra)) {
                            $product_groups[$group_key]['shippings'][$shipping_id]['data'] = $shippings_extra;

                            if (!empty($shippings_extra['delivery_time'])) {
                                $product_groups[$group_key]['shippings'][$shipping_id]['delivery_time'] = $shippings_extra['delivery_time'];
                            }
                        }
                    }
                }
            }
        }

        foreach ($product_groups as $group_key => $group) {
            if (!empty($group['chosen_shippings'])) {
                foreach ($group['chosen_shippings'] as $shipping_key => $shipping) {
                    $shipping_id = $shipping['shipping_id'];
                    $module = $shipping['module'];

                    if ($module == 'eshop_logistic' && !empty($cart['shippings_extra']['data']['eshop'][$group_key][$shipping_id])) {
                        $shipping_extra = $cart['shippings_extra']['data']['eshop'][$group_key][$shipping_id];
                        $product_groups[$group_key]['chosen_shippings'][$shipping_key]['data'] = $shipping_extra;
                    }
                }
            }
        }
    }
}
function fn_eshop_logistic_shippings_calculate_rates_post($shippings, $rates)
{
    $cart = & Tygh::$app['session']['cart'];

    if (!empty($cart['payment_method_data']['eshop_changed_payment'])) {
        unset($cart['payment_method_data']['eshop_changed_payment']);
    }
}
function fn_eshop_logistic_get_cities_pre($params, $items_per_page, $lang_code, &$fields, $condition, $join)
{
    $fields[] = '?:rus_cities.city_fias';
}
function fn_eshop_logistic_update_city_post($city_data, $city_id, $lang_code)
{
    if (!empty($city_data['city_fias']) && !empty($city_id)) {
        db_query("UPDATE ?:rus_cities SET city_fias = ?s WHERE city_id = ?i", $city_data['city_fias'], $city_id);
    }
}
function fn_eshop_logistic_update_shipping_post($shipping_data, $shipping_id, $lang_code, $action)
{   

    if (!empty($shipping_id)) {
        $service_params = !empty($shipping_data['service_params']) ? unserialize($shipping_data['service_params']) : [];

        if (!empty($shipping_data['carrier']) && $shipping_data['carrier'] == __('carrier_eshop_logistic') 
            && !empty($service_params['use_auto_image']) 
            && $service_params['use_auto_image'] == 'Y') {
                
            $service_code = db_get_field("SELECT code FROM ?:shipping_services WHERE service_id = ?i", $shipping_data['service_id']);

            if (!empty($service_code)) {
                
                $account_info = fn_eshop_logistic_get_account_full_info();
                
                if (!empty($account_info['services'])) {
                    foreach ($account_info['services'] as $eshop_service_key => $service_object) {

                        if (strpos($service_code, $eshop_service_key) !== false) {
                            
                            if (!empty($service_object->logo)) {
                                
                                
                                $_REQUEST['shipping_image_data'][] = [
                                    'pair_id'   => '',
                                    'type'      => 'M',
                                    'object_id' => $shipping_id,
                                    'image_alt' => ''
                                ];
                                $_REQUEST['file_shipping_image_icon'] = [$service_object->logo];
                                $_REQUEST['type_shipping_image_icon'] = ['url'];
                                
                                fn_attach_image_pairs('shipping', 'shipping', $shipping_id, $lang_code);

                                break;
                            }
                        }            
                    }
                }
            } 
        }
    }
}
/** HOOKS */
function fn_eshop_logistic_install()
{
    db_query("CREATE TABLE IF NOT EXISTS `?:eshop_logistic_logs` (
        `log_id` mediumint(8) unsigned NOT NULL auto_increment,
        `start_time` int(11) unsigned NOT NULL default 0,
        `time` int(11) unsigned NOT NULL default 0,
        `status` char(1) NOT NULL default 'S',
        `message` TEXT NOT NULL default '',
        `data` TEXT NOT NULL default '',
        `type` char(1) NOT NULL default 'A',
        `caching` char(1) NOT NULL default 'N',
        PRIMARY KEY  (`log_id`),
        KEY `time`(time),
        KEY `type`(type)
        ) Engine=MyISAM DEFAULT CHARSET UTF8;");

    $rus_cities_result = db_get_row("SELECT * FROM ?:rus_cities LIMIT 1");

    if (!isset($rus_cities_result['city_fias'])) {
        db_query("ALTER TABLE `?:rus_cities` ADD `city_fias` VARCHAR(128) NOT NULL DEFAULT ''");
    }

    $payments_result = db_get_row("SELECT * FROM ?:payments LIMIT 1");

    if (!isset($payments_result['eshop_payment_type'])) {
        db_query("ALTER TABLE `?:payments` ADD `eshop_payment_type` CHAR(1) NOT NULL DEFAULT 'C'");
    }

}

function fn_eshop_logistic_uninstall()
{
    db_query("DROP TABLE IF EXISTS `?:eshop_logistic_logs`");

    $rus_cities_result = db_get_row("SELECT * FROM ?:rus_cities LIMIT 1");

    if (isset($rus_cities_result['city_fias'])) {
        db_query("ALTER TABLE `?:rus_cities` DROP `city_fias`");
    }

    $payments_result = db_get_row("SELECT * FROM ?:payments LIMIT 1");

    if (isset($payments_result['eshop_payment_type'])) {
        db_query("ALTER TABLE `?:payments` DROP `eshop_payment_type`");
    }

    db_query("DELETE FROM ?:settings_objects WHERE name = ?s", 'eshop_logistic_account_info');
    db_query("DELETE FROM ?:settings_objects WHERE name = ?s", 'eshop_cache_update_time');


    $service_ids = db_get_fields('SELECT service_id FROM ?:shipping_services WHERE module = ?s', 'eshop_logistic');

    if (!empty($service_ids)) {
        db_query('DELETE FROM ?:shipping_services WHERE service_id IN (?a)', $service_ids);
        db_query('DELETE FROM ?:shipping_service_descriptions WHERE service_id IN (?a)', $service_ids);
    }
        
    
}

function fn_eshop_logistic_get_account_info()
{
    $settings = db_get_field("SELECT value FROM ?:settings_objects WHERE name = ?s", 'eshop_logistic_account_info');

    $settings = !empty($settings) ? unserialize($settings) : [];
    $settings_for_template = [];
    $formatter = Tygh::$app['formatter'];
    $account_currency_code = !empty($settings['settings']->currency) ? $settings['settings']->currency : CART_LANGUAGE;

    foreach ($settings as $setting_name => $setting_data) {
        
        switch ($setting_name) {
            case 'balance':
                $settings_for_template[$setting_name] = [
                    'description' => __('eshop_logistic.balance'),
                    'value' => $formatter->asPrice($setting_data, $account_currency_code)
                ];

                break;
            case 'time_of_request':
                $settings_for_template[$setting_name] = [
                    'description' => __('eshop_logistic.time_of_request'),
                    'value' => $formatter->asDateTime($setting_data)
                ];

                break;
            case 'services':
                $settings_for_template[$setting_name] = [
                    'description' => __('eshop_logistic.services'),
                    'services' => fn_eshop_logistic_get_services_for_template($setting_data) 
                ];

                break;
        }
    }

    return $settings_for_template;
}

function fn_eshop_logistic_get_account_full_info()
{
    $settings = db_get_field("SELECT value FROM ?:settings_objects WHERE name = ?s", 'eshop_logistic_account_info');

    $settings = !empty($settings) ? unserialize($settings) : [];
    
    return $settings;
}
function fn_eshop_logistic_get_services_for_template($services)
{   
    $service_data_for_template = [];

    foreach ($services as $service_key => $service_data) {

        if (!empty($service_data->name)) {
            $service_data_for_template[$service_key] = $service_data->name;
        }   
    }

    return $service_data_for_template;
}

function fn_eshop_logistic_get_logs($params, $lang_code = DESCR_SL, $items_per_page = 0)
{
    $default_params = array(
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    $conditions = '1';

    $fields = [
        '?:eshop_logistic_logs.*'
    ];

    $sortings = [
        'id'         => '?:eshop_logistic_logs.log_id',
        'start_time' => '?:eshop_logistic_logs.start_time',
        'time'       => '?:eshop_logistic_logs.time',
        'status'     => '?:eshop_logistic_logs.status',
        'type'       => '?:eshop_logistic_logs.type',
        'caching'    => '?:eshop_logistic_logs.caching'
    ];

    $sorting = db_sort($params, $sortings, 'id', 'desc');

    $limit  = '';
    $join   = '';

    if (!empty($params['items_per_page'])) {

        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:eshop_logistic_logs WHERE ?p", $conditions);
        $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
    }

    $logs = db_get_array("SELECT ?p FROM ?:eshop_logistic_logs ?p WHERE ?p ?p ?p", implode(',', $fields), $join, $conditions, $sorting, $limit);
    
    foreach ($logs as &$log) {

        $log['type'] = LoggerEnum::getLogTypeDescription($log['type']);
        $log['data'] = unserialize($log['data']);
    }

    return array($logs, $params);
}

function fn_eshop_logistic_clear_logs()
{
    db_query('TRUNCATE TABLE ?:eshop_logistic_logs');
}

function fn_eshop_logistic_clear_old_logs()
{
    $log_life_time = (int) Registry::get('settings.Logging.log_lifetime');

    if (!$log_life_time) {
        return;
    }

    $conditions = [
        ['start_time', '<=', strtotime(sprintf('-%d days', $log_life_time))]
    ];

    db_query('DELETE FROM ?:eshop_logistic_logs WHERE ?w', $conditions);    
}
function fn_eshop_logistic_get_eshop_payment_types()
{
    return EshopEnum::getPaymentsTypes();
}

function fn_eshop_logistic_get_eshop_payment_type_by_code($code = '')
{

    $payments = fn_eshop_logistic_get_eshop_payment_types();
    
    return !empty($payments[$code]['code']) ? $payments[$code]['code'] : false;
}
function fn_eshop_logistic_set_session_data($var, $group_key, $value, $expiry = 0)
{
    if (!isset(Tygh::$app['session']['settings'])) {
        Tygh::$app['session']['settings'] = [];
    }

    
    Tygh::$app['session']['settings'][$group_key][$var] = [
        'value' => $value,
    ];

    if (!empty($expiry)) {
        Tygh::$app['session']['settings'][$group_key][$var]['expiry'] = TIME + $expiry;
    }
    
}
function fn_eshop_logistic_delete_session_data($group_key = 'eshop_logistic')
{
    if (!empty(Tygh::$app['session']['settings'][$group_key])) {
        unset(Tygh::$app['session']['settings'][$group_key]);
    }
    
    return  true;
}

function fn_eshop_logistic_get_session_data($var = '', $group_key = '')
{
    if (!$var || !$group_key) {
        $return = [];
        foreach (Tygh::$app['session']['settings'][$group_key] as $name => $setting) {
            if (empty($setting['expiry']) || $setting['expiry'] > TIME) {
                $return[$name] = $setting['value'];
            } else {
                unset(Tygh::$app['session']['settings'][$group_key][$name]);
            }
        }
    } else {
        if (!empty(Tygh::$app['session']['settings'][$group_key][$var]) && (empty(Tygh::$app['session']['settings'][$group_key][$var]['expiry']) ||  Tygh::$app['session']['settings'][$group_key][$var]['expiry'] > TIME)) {
            $return = isset(Tygh::$app['session']['settings'][$group_key][$var]['value']) ? Tygh::$app['session']['settings'][$group_key][$var]['value'] : '';
        } else {
            if (!empty(Tygh::$app['session']['settings'][$group_key][$var])) {
                unset(Tygh::$app['session']['settings'][$group_key][$var]);
            }

            $return = false;
        }
    }

    return $return;
}

function fn_eshop_logistic_set_last_cache_update_time()
{   
    $object_id = db_get_field('SELECT object_id FROM ?:settings_objects WHERE name = ?s', 'eshop_cache_update_time');

    if (!empty($object_id)) {

        db_query('UPDATE ?:settings_objects SET value = ?s WHERE object_id = ?i', time(), $object_id);

    }else {

        $data = [
            'value' => time(),
            'name' => 'eshop_cache_update_time'
        ];

        db_query('REPLACE INTO ?:settings_objects ?e', $data);
    }
}

function fn_eshop_logistic_get_last_clear_cache_time()
{
    $time = db_get_field("SELECT value FROM ?:settings_objects WHERE name = ?s", 'eshop_cache_update_time');

    return $time;
}

function fn_eshop_logistic_clear_cache()
{
    fn_eshop_logistic_delete_session_data();
    fn_eshop_logistic_set_last_cache_update_time();
}