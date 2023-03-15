<?php

use Tygh\Registry;
use Tygh\Http;
use Tygh\Settings;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

/** SEND support information
 * @param array $params
 */
function fn_sw_telegram_send_support_info($params)
{

    $user_info = fn_get_user_short_info($params['user_id']);
    $service_id = Registry::get('addons.' . $params['addon'] . '.service_id');

    $data = [
        'dispatch' => 'support.set_chat',
        'domain'   => Registry::get('config.current_host'),
        'email' => $user_info['email'],
        'name' => $user_info['firstname'] . ' ' . $user_info['lastname'],
        'service_id' => $service_id,
        'lang_code' => DESCR_SL
    ];

    $params = array_merge($params, $data);

    unset($params['is_ajax']);

    $params['message'] = htmlspecialchars($params['message']);
    $service_id_res = (int) Http::post(SWEET_SERVER, $params);

    if (empty($service_id) && $service_id_res != false) {
        Settings::instance()->updateValue('service_id', $service_id_res, $params['addon']);
    }
    return;
}


/** Get support information
 * @param array $params
 */
function fn_sw_telegram_get_support_info($params)
{
    $data = [
        'dispatch' => 'support.review',
        'lang_code' => DESCR_SL
    ];
    $params = array_merge($params, $data);
    $support_info_query = Http::get(SWEET_SERVER, $params);

    return $support_info_query;
}

/** Get support chat data
 * @param array $params
 */
function fn_sw_telegram_get_support_info_messages($params)
{
    $data = [
        'dispatch' => 'support.get_chat',
        'service_id' => Registry::get('addons.' . $params['addon'] . '.service_id'),
        'lang_code' => DESCR_SL
    ];

    $params = array_merge($params, $data);
    unset($params['is_ajax']);

    $messages_query = (string) Http::post(SWEET_SERVER, $params);
    $messages = json_decode($messages_query, true);

    $messages_data = isset($messages[0]) ? $messages[0] : [];
    $search = isset($messages[1]) ? $messages[1] : [];

    return [$messages_data, $search];
}

/** Get support styles
 * @param array $params
 */
function fn_sw_telegram_get_support_styles()
{

    $data = [
        'dispatch' => 'support.styles',
        'lang_code' => DESCR_SL
    ];

    $style_data = Http::get(SWEET_SERVER, $data);
    return $style_data;
}

/**Systems*/
function fn_settings_actions_addons_sw_telegram(&$new_status, $old_status, $on_install)
{
    if ($new_status == 'A') {
        $new_status = fn_sw_telegram_update_addon_status_ckeckin($on_install);
    }
}

function fn_sw_telegram_update_addon_status_ckeckin($on_install, $update = false)
{
    $_ = 'sw_telegram';
    $addon_data = fn_get_addon_settings_values($_);
    $d = $_SERVER['HTTP_HOST'];
    $url = base64_decode('aHR0cHM6Ly9zd2VldGNhcnQucnU=');
    $data = array(base64_decode('ZGlzcGF0Y2g==') => base64_decode('bGljZW5zZV9jaGVja18yLmdldA=' . '='), base64_decode('aWRfYWRkb24' . '=') => $_, base64_decode('ZG9tYWlu') => $d, 'vers' => fn_get_addon_version($_), 'lang_code' => DESCR_SL);
    if ($on_install == true) {
        $data['timestamp'] = TIME;
    }
    if (fn_allowed_for('ULTIMATE')) {
        $data['ult'] = true;
    }
    if (fn_allowed_for('MULTIVENDOR')) {
        $data['mv'] = true;
    }
    $data_query = Http::get($url, $data);
    $data_query = json_decode($data_query, true);
    if (!empty($data_query)) {
        if (isset($data_query['error']) && $data_query['error'] == true) {
            fn_set_notification("W", __("warning"), $data_query['message']);
        }
        $status = $data_query['status'];
    }
    if ($update == true && $status == 'D') {
        fn_update_addon_status($_, $status);
    }
    return $status;
}

function fn_sw_telegram_add_table_cols($data)
{
    foreach ($data as $table_name => $table_fields) {

        $exist_table = db_has_table($table_name);
        if ($exist_table == false) {
            continue;
        }

        $filed_list = fn_get_table_fields($table_name);
        $index_list = db_get_array("SHOW INDEX FROM ?:$table_name");
        $index_keys = [];

        if (!empty($index_list)) {
            foreach ($index_list as $index_data) {
                $index_keys[] = $index_data['Key_name'];
            }
        }

        foreach ($table_fields as $field => $param) {
            if (in_array($field, $index_keys)) {
                db_query("ALTER TABLE ?:$table_name  DROP INDEX $field");
            }
            if (!in_array($field, $filed_list)) {
                db_query("ALTER TABLE ?:$table_name ADD $field $param");
            }
        }
    }
    return;
}
