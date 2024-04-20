<?php

defined('BOOTSTRAP') or die('Access denied');

if (!is_callable('fn_get_status_params_definition')) {
    function fn_get_status_params_definition($type) {

        $status_params = [];

        if ($type == STATUSES_ORDER) {
            $status_params = [
                'color' => [
                    'type' => 'color',
                    'label' => 'color'
                ],
                'notify' => [
                    'type' => 'checkbox',
                    'label' => 'notify_customer',
                    'default_value' => 'Y'
                ],
                'notify_department' => [
                    'type' => 'checkbox',
                    'label' => 'notify_orders_department'
                ],
                'notify_vendor' => [
                    'type' => 'checkbox',
                    'label' => 'notify_vendor'
                ],
                'inventory' => [
                    'type' => 'select',
                    'label' => 'inventory',
                    'variants' => [
                        'I' => 'increase',
                        'D' => 'decrease',
                    ],
                ],
                'payment_received' => [
                    'type'  => 'checkbox',
                    'label' => 'settled_order_status'
                ],
                'remove_cc_info' => [
                    'type' => 'checkbox',
                    'label' => 'remove_cc_info',
                    'default_value' => 'Y'
                ],
                'repay' => [
                    'type' => 'checkbox',
                    'label' => 'pay_order_again'
                ],
                'appearance_type' => [
                    'type' => 'select',
                    'label' => 'invoice_credit_memo',
                    'variants' => [
                        'D' => 'default',
                        'I' => 'invoice',
                        'C' => 'credit_memo',
                        'O' => 'order'
                    ],
                ],
            ];
            if (fn_allowed_for('MULTIVENDOR')) {
                $status_params['calculate_for_payouts'] = array(
                    'type' => 'checkbox',
                    'label' => 'charge_to_vendor_account'
                );
            } elseif (fn_allowed_for('ULTIMATE')) {
                unset($status_params['notify_vendor']);
            }
            if (Registry::get('settings.Appearance.email_templates') == 'new') {
                unset(
                    $status_params['notify'],
                    $status_params['notify_department'],
                    $status_params['notify_vendor']
                );
            }
        }

        fn_set_hook('get_status_params_definition', $status_params, $type);

        return $status_params;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'update') {
        $status_code = fn_update_status($_REQUEST['status'], $_REQUEST['status_data'], $_REQUEST['type']);
        if (!$status_code) {
            fn_set_notification('E', __('unable_to_create_status'), __('maximum_number_of_statuses_reached'));
        }
    }
    return array(CONTROLLER_STATUS_OK, 'malfunction_types.manage?type=' . $_REQUEST['type']);
}

if ($mode == 'manage') {
    $statuses = fn_get_statuses(STATUS_MALFUNCTION);
    Tygh::$app['view']->assign('statuses', $statuses);
    Tygh::$app['view']->assign('hide_email', true);
    Tygh::$app['view']->assign('type', STATUS_MALFUNCTION);
    Tygh::$app['view']->assign('status_params', fn_get_status_params_definition($_REQUEST['type']));
} elseif ($mode == 'update') {
    Tygh::$app['view']->assign('hide_email', true);

    $status_data = fn_get_status_data($_REQUEST['status'], $_REQUEST['type']);

    Tygh::$app['view']->assign('status_data', $status_data);
    Tygh::$app['view']->assign('type', $_REQUEST['type']);
    Tygh::$app['view']->assign('status_params', fn_get_status_params_definition($_REQUEST['type']));
}
