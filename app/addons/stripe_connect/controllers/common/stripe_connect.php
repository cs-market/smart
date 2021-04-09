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

use Tygh\Addons\StripeConnect\Payments\StripeConnect;
use Tygh\Common\OperationResult;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && $mode === 'check_confirmation'
) {
    /** @var \Tygh\Ajax $ajax */
    $ajax = Tygh::$app['ajax'];
    /** @var array $cart */
    $cart = Tygh::$app['session']['cart'];

    if (!empty($cart['user_data']['user_exists'])) {
        return [CONTROLLER_STATUS_NO_CONTENT];
    }

    $params = array_merge([
        'payment_intent_id' => null,
        'order_id' => null,
        'payment_id' => null,
    ], $_REQUEST);

    $total = 0;
    if ($params['order_id']) {
        $order_info = fn_get_order_info($params['order_id']);
        $total = $order_info['total'];
    } else {
        $total = $cart['total'];
        if (!empty($cart['payment_surcharge'])) {
            $total += $cart['payment_surcharge'];
        }
    }

    $payment_id = $params['payment_id'];
    if (!$payment_id) {
        $payment_id = $cart['payment_id'];
    }

    $processor = new StripeConnect(
        $payment_id,
        Tygh::$app['db'],
        Tygh::$app['addons.stripe_connect.price_formatter'],
        Tygh::$app['addons.stripe_connect.settings']
    );

    $confirmation_result = new OperationResult(false);
    try {
        $confirmation_result = $processor->getPaymentConfirmationDetails($params['payment_intent_id'], $total);
    } catch (Exception $e) {
        fn_log_event('general', 'runtime', [
            'message' => __('stripe_connect.payment_intent_error', [
                '[payment_id]' => $payment_id,
                '[error]' => $e->getMessage(),
            ]),
        ]);
    }

    if ($confirmation_result->isSuccess()) {
        foreach ($confirmation_result->getData() as $field => $value) {
            $ajax->assign($field, $value);
        }
    } else {
        $ajax->assign('error', [
            'message' => __('text_order_placed_error'),
        ]);
    }

    return [CONTROLLER_STATUS_NO_CONTENT];
}

return [CONTROLLER_STATUS_NO_PAGE];
