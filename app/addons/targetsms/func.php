<?php

use Tygh\Http;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_targetsms_get_shipping_methods() {
    return db_get_hash_single_array("SELECT a.shipping_id, b.shipping FROM ?:shippings as a LEFT JOIN ?:shipping_descriptions as b ON a.shipping_id=b.shipping_id AND b.lang_code = '" . CART_LANGUAGE . "' ORDER BY a.position", array('shipping_id', 'shipping'));
}

function fn_targetsms_get_order_statuses() {
    return db_get_hash_single_array("SELECT a.status, b.description FROM ?:statuses AS a LEFT JOIN ?:status_descriptions AS b ON a.status_id = b.status_id AND b.lang_code = ?s WHERE a.type = ?s", array('status', 'description'), CART_LANGUAGE,'O');
}

function fn_targetsms_get_form_url_info() {
    return __('targetsms_form_info_text');
}

function fn_targetsms_create_shipment(&$shipment_data, &$order_info, &$group_key, &$all_products) {
    if (Registry::get('addons.targetsms.customer_sms_create_shipments') == 'Y'){
        $notify_user = fn_get_notification_rules($_REQUEST);
        $notify_user = $notify_user['C'];
        if ($notify_user == true) {
            if (empty($order_info['b_phone']) && empty($order_info['s_phone']) && empty($order_info['phone'])) {
                return;
            }

            $path = Registry::get('config.dir.addons') . 'targetsms/';

            include ($path . 'vendor/autoload.php');

            $phone_area = Registry::get('addons.targetsms.customer_phone_field');

            $phone_field = $phone_area . '_phone';

            $phone = $order_info[$phone_field];

            $country_field = $phone_area . '_country';

            $country = $order_info[$country_field];

            if (empty($phone)) {
                // If empty this field, than try to use default phone field
                if (!empty($order_info['phone'])) {
                    $phone = $order_info['phone'];
                }
            }

            if (empty($country)) {
                if (!empty($order_info['country'])) {
                    $country = $order_info['country'];
                } else {
                    $country = Registry::get('settings.General.default_country');
                }
            }

            // Convert phone into E164 format

            $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();

            $phone_proto = $phoneUtil->parse($phone, $country);

            $phone = $phoneUtil->format($phone_proto, \libphonenumber\PhoneNumberFormat::E164);

            $shipping = db_get_field('SELECT shipping FROM ?:shipping_descriptions WHERE shipping_id = ?i AND lang_code = ?s', $shipment_data['shipping_id'], $order_info['lang_code']);

            $body = Registry::get('addons.targetsms.admin_sms_create_shipments_template');
            $replacements = array(
                '%LANG_NEW_SHIPMENT%' => __("addons.targetsms.new_shipment_sms", 
                    ["[order_id]" => $order_info['order_id'], "[shipping_method]" => $shipping, "[tracking_number]" => $shipment_data['tracking_number']])
            );
            $body = str_replace(array_keys($replacements), $replacements, $body);
            $body = fn_targetsms_strip_tags($body);

            $company_info = fn_get_company_data($order_info['company_id']);
            $company_info['sms_sender_name'] = fn_get_company_sender($order_info['company_id']);

            fn_targetsms_send_sms($phone, $body, $company_info['sms_sender_name'], "createshipment");
        }
    }
}

function fn_targetsms_change_order_status(&$status_to, &$status_from, &$order_info, &$force_notification, &$order_statuses, &$place_order) {

    $is_check = true;

    if ($_REQUEST['dispatch'] == 'order_management.place_order.save'){
        if ($_REQUEST['notify_vendor_sms'] != 'Y'){
            $is_check = false;
        }
    }
    if ($is_check){
        if (Registry::get('addons.targetsms.admin_sms_order_updated') == 'Y' || Registry::get('addons.targetsms.customer_sms_order_updated') == 'Y') {

            $order_id = $order_info['order_id'];
            $company_info = fn_get_company_data($order_info['company_id']);

            Tygh::$app['view']->assign('order_id', $order_id);
            Tygh::$app['view']->assign('total', $order_info['total']);
            Tygh::$app['view']->assign('order_email', $order_info['email']);
            Tygh::$app['view']->assign('order_payment_info', $order_info['payment_method']['payment']);
            Tygh::$app['view']->assign('order_status_name', $order_statuses[$status_to]['description']);

            $body = '';

            if (Registry::get('addons.targetsms.admin_sms_order_updated') == 'Y') {
                $result = fn_targetsms_check_order_conditions('admin', $status_to, $order_info, $order_statuses);
                if ($result == true) {
                    $body = Registry::get('addons.targetsms.admin_sms_order_update_template');
                    $replacements = array(
                        '%ORDER_ID%' => $order_id,
                        '%ORDER_STATUS_NAME%' => $order_statuses[$status_to]['description'],
                        '%TOTAL%' => $order_info['total'],
                        '%ORDER_EMAIL%' => $order_info['email'],
                        '%ORDER_PAYMENT_INFO%' => $order_info['payment_method']['payment'],
                        '%LANG_ORDER%' => __("order"),
                        '%LANG_FOR_THE_SUM%' => __("sms_for_the_sum"),
                        '%LANG_IS%' => __("addons.targetsms.is")
                    );
                    $body = str_replace(array_keys($replacements), $replacements, $body);
                    $body = fn_targetsms_strip_tags($body);

                    $phone = Registry::get('addons.targetsms.admin_phone_number');
                    $phone = explode(',', $phone);

                    $company_info['sms_sender_name'] = fn_get_company_sender($company_info['company_id']);
                    foreach($phone as $k => $v) {
                        fn_targetsms_send_sms($v, $body,$company_info['sms_sender_name'],"changeorderstatus");
                    }
                }
            }

            if (Registry::get('addons.targetsms.customer_sms_order_updated') == 'Y') {

                if (empty($order_info['b_phone']) && empty($order_info['s_phone']) && empty($order_info['phone'])) {
                    return;
                }

                $path = Registry::get('config.dir.addons') . 'targetsms/';

                include ($path . 'vendor/autoload.php');

                $phone_area = Registry::get('addons.targetsms.customer_phone_field');

                $phone_field = $phone_area . '_phone';

                $phone = $order_info[$phone_field];

                $country_field = $phone_area . '_country';

                $country = $order_info[$country_field];

                if (empty($phone)) {
                    // If empty this field, than try to use default phone field
                    if (!empty($order_info['phone'])) {
                        $phone = $order_info['phone'];
                    }
                }

                if (empty($country)) {
                    if (!empty($order_info['country'])) {
                        $country = $order_info['country'];
                    } else {
                        $country = Registry::get('settings.General.default_country');
                    }
                }

                // Convert phone into E164 format

                $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();

                $phone_proto = $phoneUtil->parse($phone, $country);

                $phone = $phoneUtil->format($phone_proto, \libphonenumber\PhoneNumberFormat::E164);

                $result = fn_targetsms_check_order_conditions('customer', $status_to, $order_info, $order_statuses);
                list($shipments) = fn_get_shipments_info(array('order_id' => $order_id, 'advanced_info' => true));
                if (!empty($shipments[0])){
                    $ship_track_number = $shipments[0]['tracking_number'];
                }
                if ($result == true) {
                    if ($order_statuses[$status_to]['sms_body']) {

                        $replacements = array(
    			            '%ORDER_ID%' => $order_id,
                            '%AMOUNT%' => $order_info['total'],
                            '%NAME%' => $order_info['firstname'],
                            '%LAST_NAME%' => $order_info['lastname'],
                            '%USER_EMAIL%' => $order_info['email'],
                            '%COUNTRY%' => fn_get_country_name($order_info[$phone_area . '_country']),
                            '%ADDRESS%' => $order_info[$phone_area . '_address'],
                            '%CITY%' => $order_info[$phone_area . '_city'],
                            '%STATE%' => $order_info[$phone_area . '_state'],
                            '%PHONE%' => $order_info['phone'],
                            '%TRACK_NUMBER%' => $ship_track_number
                        );

                        $body = $order_statuses[$status_to]['sms_body'];
                        $body = str_replace(array_keys($replacements), $replacements, $body);
                        $company_info['sms_sender_name'] = fn_get_company_sender($company_info['company_id']);

                        $body = fn_targetsms_strip_tags($body);
                        fn_targetsms_send_sms($phone, $body,$company_info['sms_sender_name'],'changeorderstatus');
                    }
                }
            }
        }
    }
}

function fn_targetsms_place_order(&$order_id, &$action, &$fake1, &$cart) {
    if ($action !== 'save' && Registry::get('addons.targetsms.admin_sms_new_order_placed') == 'Y') {
        Tygh::$app['view']->assign('order_id', $order_id);
        Tygh::$app['view']->assign('total', $cart['total']);

        $send_info = Registry::get('addons.targetsms.admin_sms_send_payment_info');
        $send_email = Registry::get('addons.targetsms.admin_sms_send_customer_email');
        $send_min_amount = Registry::get('addons.targetsms.admin_sms_send_min_amout');
        $shippings = Registry::get('addons.targetsms.admin_sms_send_shipping');

        if (!is_array($shippings)) {
            $shippings = array ();
        }
        $order = fn_get_order_info($order_id);
        $company_info = fn_get_company_data($order['company_id']);

        Tygh::$app['view']->assign('order_email', $order['email']);
        Tygh::$app['view']->assign('order_payment_info', $order['payment_method']['payment']);

        if (count($shippings) && !isset($shippings['N'])) {
            $in_shipping = false;

            if (!empty($order['shipping'])) {
                foreach ($order['shipping'] as $data) {
                    $id = $data['shipping_id'];
                    if ($shippings[$id] == 'Y') {
                        $in_shipping = true;
                        break;
                    }
                }
            }
        } else {
            $in_shipping = true;
        }

        if ($in_shipping && $order['subtotal'] > doubleval($send_min_amount)) {
            $body = Registry::get('addons.targetsms.admin_sms_order_place_template');
            $replacements = array(
                '%ORDER_ID%' => $order_id,
                '%TOTAL%' => $order['total'],
                '%ORDER_EMAIL%' => $order['email'],
                '%ORDER_PAYMENT_INFO%' => $order['payment_method']['payment'],
                '%LANG_ORDER%' => __("order"),
                '%LANG_FOR_THE_SUM%' => __("sms_for_the_sum"),
                '%LANG_IS%' => __("addons.targetsms.is"),
                '%LANG_ORDER_PLACED%' => __("sms_order_placed"),
                '%LANG_PAYMENT_INFO%' => __("payment_info"),
                '%LANG_CUSTOMER_EMAIL%' => __("customer_email")                
            );
            $body = str_replace(array_keys($replacements), $replacements, $body);
            $body = fn_targetsms_strip_tags($body);
            $phone = Registry::get('addons.targetsms.admin_phone_number');
            $phone = explode(',', $phone);

			$company_info['sms_sender_name'] = fn_get_company_sender($company_info['company_id']);

            foreach($phone as $k => $v) {
                fn_targetsms_send_sms($v, $body,$company_info['sms_sender_name'],'placeorder');
            }
        }
    }
}

function fn_targetsms_update_profile(&$action, &$user_data) {
    if ($action == 'add' && AREA == 'C' && Registry::get('addons.targetsms.admin_sms_new_cusomer_registered') == 'Y') {
        $customer = $user_data['firstname'] . (empty($user_data['lastname']) ? '' : $user_data['lastname']);
        $body = Registry::get('addons.targetsms.admin_sms_profile_update_template');
        $replacements = array(
            '%LANG_CUSTOMER_REGISTRED%' => __("sms_customer_registered", ["[name]" => $customer])              
        );
        $body = str_replace(array_keys($replacements), $replacements, $body);
        $body = fn_targetsms_strip_tags($body);
        $phone = Registry::get('addons.targetsms.admin_phone_number');
        $phone = explode(',', $phone);
        $company_info = fn_get_company_data($user_data['company_id']);

        $company_info['sms_sender_name'] = fn_get_company_sender($company_info['company_id']);
        foreach($phone as $k => $v) {
            fn_targetsms_send_sms($v, $body,$company_info['sms_sender_name'], 'updateprofile');
        }
    }
}

function fn_targetsms_update_product_amount(&$new_amount, &$product_id) {
    if ($new_amount <= Registry::get('settings.General.low_stock_threshold') && Registry::get('addons.targetsms.admin_sms_product_negative_amount') == 'Y') {
        $lang_code = Registry::get('settings.Appearance.backend_default_language');
        $product = db_get_field("SELECT product FROM ?:product_descriptions WHERE product_id = ?i AND lang_code = ?s", $product_id, $lang_code);
        $body = Registry::get('addons.targetsms.admin_sms_update_product_amount_template');
        $replacements = array(
            '%LANG_LOW_STOCK_SUBJ%' => (__("low_stock_subj", ["[product]" => "$product #$product_id"])),
            '%COMPANY_NAME%' => Registry::get('settings.Company.company_name')
        );
        $body = str_replace(array_keys($replacements), $replacements, $body);
        $body = fn_targetsms_strip_tags($body);
        $phone = Registry::get('addons.targetsms.admin_phone_number');
        $phone = explode(',', $phone);
        $company_info = fn_get_company_data($product['company_id']);
        $company_info['sms_sender_name'] = fn_get_company_sender($company_info['company_id']);
        foreach($phone as $k => $v) {
            fn_targetsms_send_sms($v, $body,$company_info['sms_sender_name'],'updateamount');
        }
    }
}

function fn_targetsms_check_order_conditions($for = 'admin', $status_to, $order, $order_statuses) {
    $send_min_amount = Registry::get('addons.targetsms.' . $for . '_sms_send_min_amount');
    $shippings = Registry::get('addons.targetsms.' . $for . '_sms_send_shipping');
    $statuses = Registry::get('addons.targetsms.' . $for . '_sms_send_order_statuses');
    if ($for == 'admin') {
        $send_email = Registry::get('addons.targetsms.admin_sms_send_customer_email');
    }

    if (!is_array($statuses)) {
        $statuses = array();
    }

    if (!is_array($shippings)) {
        $shippings = array ();
    }

    if (count($shippings) && !isset($shippings['N'])) {
        $in_shipping = false;

        if (!empty($order['shipping'])) {
            foreach ($order['shipping'] as $data) {
                $id = $data['shipping_id'];
                if ($shippings[$id] == 'Y') {
                    $in_shipping = true;
                    break;
                }
            }
        }
    } else {
        $in_shipping = true;
    }

    if (count($statuses) && !isset($statuses['N'])) {
        $in_status = false;
        if ($statuses[$status_to] == 'Y') {
            $in_status = true;
        }
        // check if status N is a status
        if (isset($statuses['N']) && empty($statuses['N'])) {
            $in_status = true;
        }
    } else {
        $in_status = true;
    }

    if ($in_status == true && $in_shipping == true && $order['subtotal'] > doubleval($send_min_amount)) {
        return true;
    }
    return false;
}

function fn_custom_sms_send($order_id, $text) {
    Tygh::$app['view']->assign('order_id', $order_id);
    $send_info = Registry::get('addons.targetsms.admin_sms_send_payment_info');
    $send_email = Registry::get('addons.targetsms.admin_sms_send_customer_email');
    $send_min_amount = Registry::get('addons.targetsms.admin_sms_send_min_amout');
    $shippings = Registry::get('addons.targetsms.admin_sms_send_shipping');

    if (!is_array($shippings)) {
            $shippings = array ();
    }

    $order = fn_get_order_info($order_id);
    $company_info = fn_get_company_data($order['company_id']);

    Tygh::$app['view']->assign('order_email', $order['email']);
    Tygh::$app['view']->assign('order_payment_info', $order['payment_method']['payment']);

    if (count($shippings) && !isset($shippings['N'])) {
        $in_shipping = false;
        if (!empty($order['shipping'])) {
            foreach ($order['shipping'] as $data) {
                $id = $data['shipping_id'];
                if ($shippings[$id] == 'Y') {
                    $in_shipping = true;
                    break;
                }
            }
        }
    } else {
        $in_shipping = true;
    }

    if ($in_shipping && $order['subtotal'] > doubleval($send_min_amount)) {
        $body = $text;
        $replacements = array(
            '%ORDER_ID%' => $order_id,
            '%TOTAL%' => $order['total'],
            '%ORDER_EMAIL%' => $order['email'],
            '%ORDER_PAYMENT_INFO%' => $order['payment_method']['payment'],
            '%LANG_ORDER%' => __("order"),
            '%LANG_FOR_THE_SUM%' => __("sms_for_the_sum"),
            '%LANG_IS%' => __("addons.targetsms.is"),
            '%LANG_ORDER_PLACED%' => __("sms_order_placed"),
            '%LANG_PAYMENT_INFO%' => __("payment_info"),
            '%LANG_CUSTOMER_EMAIL%' => __("customer_email")                
        );
        $body = str_replace(array_keys($replacements), $replacements, $body);
        $body = fn_targetsms_strip_tags($body);

        $phone = Registry::get('addons.targetsms.admin_phone_number');
        $phone = explode(',', $phone);

        $phone = $order['phone'];

        if ($order['b_phone']){
            $phone = $order['b_phone'];
        }else{
            if ($order['s_phone']){
                $phone = $order['s_phone'];
            }
        }

		$company_info['sms_sender_name'] = fn_get_company_sender($company_info['company_id']);

        fn_targetsms_send_sms($phone, $body,$company_info['sms_sender_name'],'sendmanualsms');
    }
}

function fn_targetsms_get_companies($params, &$fields, $sortings, $condition, $join, $auth, $lang_code, $group){
    array_push($fields, '?:companies.sms_sender_name');
}

function fn_targetsms_send_sms($phone, $body, $sender, $source) {
    //fn_print_die($phone, $body, $sender, $source);    
    $phone = trim($phone);
    $versions = explode(".", PRODUCT_VERSION);
    $source = "cscartorders" . $versions[0] . "_" . $versions[1] . "_" . $source; 

    if (!$phone || !$body) {
        return false;
    }

    $first_symbol = substr($phone, 0, 1);

    if ($first_symbol == '+') {
        $phone = substr($phone, 1);
    }
    if ($first_symbol == '8') {
        $phone = substr($phone, 1);
        $phone = "7" . $phone;
    }

    $fail_responces = array(
        'У нас закончились SMS. Для разрешения проблемы свяжитесь с менеджером.',
        'Закончились SMS.',
        'Аккаунт заблокирован.',
        'Укажите номер телефона.',
        'Номер телефона присутствует в стоп-листе.',
        'Данное направление закрыто для вас.',
        'Данное направление закрыто.',
        'Недостаточно средств для отправки SMS. SMS будет отправлена как только вы пополните счет по данному направлению.',
        'Текст SMS отклонен модератором.',
        'Нет отправителя.',
        'Отправитель не должен превышать 15 символов для цифровых номеров и 11 символов для буквенно-числовых.',
        'Номер телефона должен быть меньше 15 символов.',
        'Нет текста сообщения.',
        'Нет ссылки.',
        'Такого отправителя нет.',
        'Отправитель не прошел модерацию.',
        'error: Попытка отправки более одного одинакового запроса в течение минуты'
    );
    $data = array(
        'user' => Registry::get('addons.targetsms.targetsms_login'),
        'pwd' => Registry::get('addons.targetsms.targetsms_password'),
        'sadr' => $sender,
        'dadr' => $phone,
        'text' => $body,
        'name_delivery' => $source
    );
    $targetsms_host = "sms.targetsms.ru";
    $result = Http::get('https://' . $targetsms_host . '/sendsms.php', $data);
    if (AREA == 'A' && in_array($result, $fail_responces)) {
        fn_set_notification('W', __('warning'), $result);
    }
}

function fn_targetsms_get_auth() {
    return array(
        'login' => Registry::get('addons.targetsms.targetsms_login'),
        'psw' => Registry::get('addons.targetsms.targetsms_password')
    );
}

/**
 * Strip html tags from the data
 *
 * @param mixed $var variable to strip tags from
 * @return mixed filtered variable
 */
function fn_targetsms_strip_tags(&$var)
{

    if (!is_array($var)) {
        return (strip_tags($var));
    } else {
        $stripped = array();
        foreach ($var as $k => $v) {
            $sk = strip_tags($k);
            if (!is_array($v)) {
                $sv = strip_tags($v);
            } else {
                $sv = fn_strip_tags($v);
            }
            $stripped[$sk] = $sv;
        }

        return ($stripped);
    }
}

function fn_settings_variants_addons_targetsms_targetsms_sender(){
    $src = '<?xml version="1.0" encoding="utf-8"?><request><security><login value="' . Registry::get('addons.targetsms.targetsms_login') . '" /><password value="' . Registry::get('addons.targetsms.targetsms_password') .'" /></security></request>';
    $href = "https://sms.targetsms.ru/xml/originator.php";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: text/xml; charset=utf-8')); curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CRLF, true); curl_setopt($ch,CURLOPT_POST, true); 
    curl_setopt($ch,CURLOPT_POSTFIELDS, $src); 
    curl_setopt($ch,CURLOPT_URL, $href);
    $result = curl_exec($ch); 
    $res = $result; 
    curl_close($ch);

    $xml = simplexml_load_string($res);
    $json = json_encode($xml);
    $php_array = json_decode($json, true);
    $sender_names = [];   
    if (count($php_array['list_originator']['originator']) == 1){
    	$sender_names[$php_array['list_originator']['originator']] = $php_array['list_originator']['originator'];
    }else{
	    if (!empty($php_array['list_originator']['originator'])){
		    foreach ($php_array['list_originator']['originator'] as $key => $sender) {   
		        $sender_names[$sender] = $sender;
		    }
	    }
    }
    if (empty($php_array['error'])){
        return $sender_names;
    }else{
        fn_targetsms_show_error($php_array['error']);
        $settings = ["target"];
        return $settings;
    }
}

function fn_settings_variants_addons_targetsms_custom_sms_sender(){
    $src = '<?xml version="1.0" encoding="utf-8"?><request><security><login value="' . Registry::get('addons.targetsms.targetsms_login') . '" /><password value="' . Registry::get('addons.targetsms.targetsms_password') .'" /></security></request>';
    $href = "https://sms.targetsms.ru/xml/originator.php";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: text/xml; charset=utf-8')); curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CRLF, true); curl_setopt($ch,CURLOPT_POST, true); 
    curl_setopt($ch,CURLOPT_POSTFIELDS, $src); 
    curl_setopt($ch,CURLOPT_URL, $href);
    $result = curl_exec($ch); 
    $res = $result; 
    curl_close($ch);

    $xml = simplexml_load_string($res);
    $json = json_encode($xml);
    $php_array = json_decode($json, true);
    $sender_names = [];   
    if (count($php_array['list_originator']['originator']) == 1){
        $sender_names[$php_array['list_originator']['originator']] = $php_array['list_originator']['originator'];
    }else{
        if (!empty($php_array['list_originator']['originator'])){
            foreach ($php_array['list_originator']['originator'] as $key => $sender) {   
                $sender_names[$sender] = $sender;
            }
        }
    }
    if (empty($php_array['error'])){
        return $sender_names;
    }else{
        fn_targetsms_show_error($php_array['error']);
        $settings = ["target"];
        return $settings;
    }
}

function fn_targetsms_get_balance(){
    $src = '<?xml version="1.0" encoding="utf-8"?><request><security><login value="' . Registry::get('addons.targetsms.targetsms_login') . '" /><password value="' . Registry::get('addons.targetsms.targetsms_password') .'" /></security></request>';
    $href = 'https://sms.targetsms.ru/xml/balance.php'; 
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: text/xml; charset=utf-8')); curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CRLF, true); curl_setopt($ch,CURLOPT_POST, true); 
    curl_setopt($ch,CURLOPT_POSTFIELDS, $src); 
    curl_setopt($ch,CURLOPT_URL, $href);
    $result = curl_exec($ch); 
    $res = $result; 
    curl_close($ch);

    $xml = simplexml_load_string($res);
    $json = json_encode($xml);
    $php_array = json_decode($json, true);

    if (empty($php_array['error'])){
        $result = "<p>" .__('your_balance') . $php_array['money'] . __('balance_currency') ."</p><p><a class='btn btn-primary' target='_blank' href='". __('rechardge_href') ."'>". __('rechardge_btn') . "</a></p>";
        return $result;
    }else{
        fn_targetsms_show_error($php_array['error']);
        return "";
    }
}

function fn_targetsms_show_error($error_text){
    if ($error_text == 'Имена отправителей не обнаружены'){
        fn_set_notification('E', __('error'),  __('sender_names_not_detect'));
    }else if ($error_text == 'Неправильный логин или пароль'){
        fn_set_notification('E', __('error'),  __('wrong_login_or_password'));
    }else{
        fn_set_notification('E', __('error'),  $error_text);
    }    
}

function fn_get_company_sender($company_id){
   $auth = Tygh::$app['session']['auth'];
   $companies = fn_get_companies(array(), $auth);
   $sender = '';
   foreach ($companies[0] as $key => $company) {
        if ($company['company_id'] == $company_id){
            $sender = $company['sms_sender_name'];
        }
   }
	return $sender;
}

function fn_targetsms_send_btn(){
	$btn = '<div style="text-align:right;margin-right:50px;"><input class="btn btn-primary" id="send_sms_custom" type="button" value="' . __('send_sms') .'"></div>';
	return $btn;
}
function fn_get_senders_data(){
    $auth = Tygh::$app['session']['auth'];
    $companies = fn_get_companies($params, $auth);
    $senders = fn_settings_variants_addons_targetsms_targetsms_sender();
    $data['companies'] = $companies[0];
    $data['senders'] = $senders;
    return $data;
}