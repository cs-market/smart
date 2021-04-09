<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

defined('BOOTSTRAP') or die('Access denied');

use Tygh\Addons\StripeConnect\Payments\StripeConnect;
use Tygh\Registry;

/**
 * Installs Stripe Connect payment processor.
 */
function fn_stripe_connect_add_payment_processor()
{
    /** @var \Tygh\Database\Connection $db */
    $db = Tygh::$app['db'];

    if (!$db->getField('SELECT type FROM ?:payment_processors WHERE processor_script = ?s',
        StripeConnect::getScriptName())) {
        $db->query('INSERT INTO ?:payment_processors ?e', array(
            'processor'          => __('stripe_connect.stripe_connect'),
            'processor_script'   => StripeConnect::getScriptName(),
            'processor_template' => 'addons/stripe_connect/views/orders/components/payments/stripe_connect.tpl',
            'admin_template'     => 'stripe_connect.tpl',
            'callback'           => 'Y',
            'type'               => 'P',
            'addon'              => StripeConnect::getPaymentName(),
        ));
    }
}

/**
 * Disables Stripe Connect payment methods upon add-on uninstallation.
 */
function fn_stripe_connect_remove_payment_processor()
{
    /** @var \Tygh\Database\Connection $db */
    $db = Tygh::$app['db'];

    $processor_id = $db->getField(
        'SELECT processor_id FROM ?:payment_processors WHERE processor_script = ?s',
        StripeConnect::getScriptName()
    );

    if (!$processor_id) {
        return;
    }

    $db->query('DELETE FROM ?:payment_processors WHERE processor_id = ?i', $processor_id);
    $db->query(
        'UPDATE ?:payments SET ?u WHERE processor_id = ?i',
        array(
            'processor_id'     => 0,
            'processor_params' => '',
            'status'           => 'D',
        ),
        $processor_id
    );
}

/**
 * Hook handler: removes Stripe Connect method from customer area when products' vendor has no Stripe account connected.
 *
 * @param array  $params
 * @param string $fields
 * @param string $join
 * @param string $order
 * @param array  $condition
 * @param string $having
 */
function fn_stripe_connect_get_payments(&$params, &$fields, &$join, &$order, &$condition, &$having)
{
    if (($params['area'] === 'C' || defined('ORDER_MANAGEMENT'))
        && !empty(Tygh::$app['session']['cart']['product_groups'])
    ) {
        foreach (Tygh::$app['session']['cart']['product_groups'] as $product_group) {
            if (!StripeConnect::getChargeReceiver($product_group['company_id'])) {
                $condition[] = db_quote(
                    '(?:payment_processors.processor_script IS NULL'
                    . ' OR ?:payment_processors.processor_script <> ?s)',
                    StripeConnect::getScriptName()
                );
                break;
            }
        }
    }
}

/**
 * Hook handler: performs refund via Stripe Connect when returning order/products via RMA add-on.
 *
 * @param array  $data
 * @param bool   $show_confirmation_page
 * @param bool   $show_confirmation
 * @param bool   $is_refund
 * @param array  $_data
 * @param string $confirmed
 */
function fn_stripe_connect_rma_update_details_post(
    &$data,
    &$show_confirmation_page,
    &$show_confirmation,
    &$is_refund,
    &$_data,
    &$confirmed
) {
    if (empty($data['change_return_status']['stripe_connect_perform_refund'])) {
        return;
    }

    $change_return_status = $data['change_return_status'];

    $order_info = fn_get_order_info($change_return_status['order_id']);
    $return_statuses = fn_get_statuses(STATUSES_RETURN);

    if ($change_return_status['status_to'] != $change_return_status['status_from']
        && $return_statuses[$change_return_status['status_to']]['params']['inventory'] != 'D'
        && !empty($order_info['payment_method']['processor_params']['is_stripe_connect'])
        && !empty($order_info['payment_info']['stripe_connect.charge_id'])
        && empty($order_info['payment_info']['stripe_connect.refund_id'])
    ) {
        $amount = 0;

        $return_data = fn_get_return_info($change_return_status['return_id']);

        if (!empty($order_info['returned_products'])) {
            foreach ($order_info['returned_products'] as $cart_id => $product) {
                if (isset($return_data['items']['A'][$cart_id])) {
                    $amount += $product['subtotal'];
                }
            }
        } elseif (!empty($order_info['products'])) {
            foreach ($order_info['products'] as $cart_id => $product) {
                if (isset($product['extra']['returns']) && isset($return_data['items']['A'][$cart_id])) {
                    foreach ($product['extra']['returns'] as $return_id => $product_return_data) {
                        $amount += $return_data['items']['A'][$cart_id]['price'] * $product_return_data['amount'];
                    }
                }
            }
        }

        if ($amount) {
            $payment_processor = new StripeConnect(
                $order_info['payment_method']['payment_id'],
                Tygh::$app['db'],
                Tygh::$app['addons.stripe_connect.price_formatter'],
                Tygh::$app['addons.stripe_connect.settings'],
                $order_info['payment_method']['processor_params']
            );

            try {
                /**
                 * @var array{order_id: int, company_id:int, payment_info:array{'stripe_connect.transfer_id':string, 'stripe_connect.charge_id':string}} $order_info
                 */
                $refund_id = $payment_processor->refund($order_info, $amount);

                fn_update_order_payment_info($order_info['order_id'], array(
                    'stripe_connect.refund_id' => $refund_id,
                ));

                if ($order_status = Registry::get('addons.stripe_connect.rma_refunded_order_status')) {
                    fn_change_order_status($order_info['order_id'], $order_status, '', false);
                }

                fn_set_notification('N', __('notice'), __('stripe_connect.rma.refund_performed'));
            } catch (Exception $e) {
                fn_set_notification('E', __('error'), $e->getMessage());
            }
        }
    }
}

/**
 * Hook handler: adds stripe_connect_account_id into the list of selected from ?:companies table fields.
 *
 * @param array  $params
 * @param array  $fields
 * @param array  $sortings
 * @param string $condition
 * @param string $join
 * @param array  $auth
 * @param string $lang_code
 * @param string $group
 */
function fn_stripe_connect_get_companies(
    &$params,
    &$fields,
    &$sortings,
    &$condition,
    &$join,
    &$auth,
    &$lang_code,
    &$group
) {
    $fields[] = db_quote('?:companies.stripe_connect_account_id');
}

/**
 * The "prepare_checkout_payment_methods_before_get_payments" hook handler.
 *
 * Actions performed:
 *  - Adds company_id into get payments params on repay
 *
 * @see fn_prepare_checkout_payment_methods()
 */
function fn_stripe_connect_prepare_checkout_payment_methods_before_get_payments(
    $cart,
    $auth,
    $lang_code,
    $get_payment_groups,
    $payment_methods,
    &$get_payments_params
)
{
    if (!empty($cart['order_id']) && !empty($cart['company_id'])) {
        $get_payments_params['company_id'] = $cart['company_id'];
    }
}

/**
 * The "prepare_checkout_payment_methods_after_get_payments" hook handler.
 *
 * Actions performed:
 *  - Excludes stripe connect payment by script name from payments selection if vendor have not Stripe account ID
 *
 * @see fn_prepare_checkout_payment_methods()
 */
function fn_stripe_connect_prepare_checkout_payment_methods_after_get_payments(
    $cart,
    $auth,
    $lang_code,
    $get_payment_groups,
    &$payment_methods,
    $get_payments_params,
    $cache_key
)
{
    if (
        !empty($payment_methods[$cache_key])
        && !empty($get_payments_params['company_id'])
        && !StripeConnect::getChargeReceiver($get_payments_params['company_id'])
    ) {
        foreach ($payment_methods[$cache_key] as $payment_id => $payment_method) {
            if ($payment_method['processor_script'] == StripeConnect::getScriptName()) {
                unset($payment_methods[$cache_key][$payment_id]);
            }
        }
    }
}
