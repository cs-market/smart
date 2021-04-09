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

namespace Tygh\Addons\YandexCheckout\Payments;

use Tygh\Addons\YandexCheckout\Services\ReceiptService;
use Tygh\Enum\YesNo;
use YandexCheckout\Client;

/**
 * Class YandexCheckout
 *
 * @package Tygh\Addons\YandexCheckout
 */
class YandexCheckout
{
    /** @var string  */
    protected $shop_id;

    /** @var string  */
    protected $secret_key;

    /** @var \YandexCheckout\Client */
    protected $client;

    /** @var \Tygh\Addons\YandexCheckout\Services\ReceiptService */
    protected $receipt_service;

    public function __construct($shop_id, $secret_key, ReceiptService $receipt_service)
    {
        $this->client = new Client();
        $this->shop_id = $shop_id;
        $this->secret_key = $secret_key;
        $this->receipt_service = $receipt_service;
        $this->authorize();
    }

    /**
     *
     */
    protected function authorize()
    {
        $this->client->setAuth($this->shop_id, $this->secret_key);
    }

    /**
     * Creates attribute 'amount' for Payment.
     *
     * @param float  $order_total Order total sum.
     * @param string $currency    Payment processor parameters.
     *
     * @return array{currency: string, value: float}
     */
    protected function createPaymentAmount($order_total, $currency)
    {
        return [
            'value' => ($currency !== CART_PRIMARY_CURRENCY)
                ? fn_format_price_by_currency($order_total, CART_PRIMARY_CURRENCY, $currency)
                : $order_total,
            'currency' => $currency,
        ];
    }

    /**
     * Creates payment at Yandex.Checkout server side.
     *
     * @param array<string, string|float> $order_info       Order information.
     * @param array<string, string>       $processor_params Payment processor parameters.
     *
     * @return \YandexCheckout\Request\Payments\CreatePaymentResponse
     *
     * @throws \YandexCheckout\Common\Exceptions\ApiException                Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\BadApiRequestException      Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\ForbiddenException          Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\InternalServerError         Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\NotFoundException           Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\ResponseProcessingException Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\TooManyRequestsException    Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\UnauthorizedException       Yandex.Checkout API Exception.
     */
    public function createPayment(array $order_info, array $processor_params)
    {
        $params = [
            'amount' => $this->createPaymentAmount((float) $order_info['total'], $processor_params['currency']),
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => fn_url('yandex_checkout.return_to_store&order_id=' . $order_info['order_id']),
            ],
            'capture' => !YesNo::toBool($processor_params['are_held_payments_enabled']),
            'metadata' => [
                'order_id' => $order_info['order_id'],
            ],
        ];

        if (!empty($processor_params['selected_payment_method'])) {
            $params['payment_method_data'] = [
                'type' => $processor_params['selected_payment_method'],
            ];
        }

        if (YesNo::toBool($processor_params['send_receipt'])) {
            $receipt = $this->receipt_service->getReceiptFromOrder($order_info, 'full_prepayment');
            $params['receipt'] = $receipt;
        }

        return $this->client->createPayment($params);
    }

    /**
     * @param $payment_id
     *
     * @return \YandexCheckout\Model\PaymentInterface
     *
     * @throws \YandexCheckout\Common\Exceptions\ApiException                Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\BadApiRequestException      Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\ExtensionNotFoundException  Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\ForbiddenException          Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\InternalServerError         Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\NotFoundException           Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\ResponseProcessingException Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\TooManyRequestsException    Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\UnauthorizedException       Yandex.Checkout API Exception.
     */
    public function getPaymentInfo($payment_id)
    {
        return $this->client->getPaymentInfo($payment_id);
    }

    /**
     * @param array $order_info
     *
     * @return \YandexCheckout\Request\Receipts\AbstractReceiptResponse|null
     *
     * @throws \YandexCheckout\Common\Exceptions\BadApiRequestException      Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\ApiConnectionException      Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\ApiException                Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\AuthorizeException          Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\ForbiddenException          Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\InternalServerError         Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\NotFoundException           Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\ResponseProcessingException Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\TooManyRequestsException    Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\UnauthorizedException       Yandex.Checkout API Exception.
     */
    public function createReceipt(array $order_info)
    {
        $receipt = $this->receipt_service->getPaymentReceiptFromOrder($order_info);
        return $this->client->createReceipt($receipt);
    }

    /**
     * Gets payment methods connected to Yandex.Checkout account.
     *
     * @return array<string>
     *
     * @throws \YandexCheckout\Common\Exceptions\BadApiRequestException      Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\ApiException                Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\AuthorizeException          Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\ExtensionNotFoundException  Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\ForbiddenException          Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\InternalServerError         Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\NotFoundException           Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\ResponseProcessingException Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\TooManyRequestsException    Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\UnauthorizedException       Yandex.Checkout API Exception.
     */
    public function getPaymentMethods()
    {
        $account = $this->client->me();

        return isset($account['payment_methods']) ? $account['payment_methods'] : [];
    }

    /**
     * @param array<string, array<string, string>> $order_info       Order information.
     * @param array<string, string>                $processor_params Payment processor parameters.
     *
     * @return \YandexCheckout\Request\Payments\Payment\CreateCaptureResponse
     *
     * @throws \YandexCheckout\Common\Exceptions\BadApiRequestException      Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\ApiException                Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\ForbiddenException          Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\InternalServerError         Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\NotFoundException           Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\ResponseProcessingException Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\TooManyRequestsException    Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\UnauthorizedException       Yandex.Checkout API Exception.
     * @throws \Exception                                                    Yandex.Checkout API Exception.
     */
    public function capturePayment(array $order_info, array $processor_params)
    {
        return $this->client->capturePayment(
            [
                'amount' => $this->createPaymentAmount((float) $order_info['total'], $processor_params['currency']),
            ],
            $order_info['payment_info']['payment_id']
        );
    }

    /**
     * Creates refund request in Yandex.Checkout.
     *
     * @param array{amount: array{currency: string, value: float}, payment_id: string, receipt?: array{customer: array{email: string, phone: string}, items: list<array{amount: array{currency: string, value: float}, description: string, quantity: float, vat_code: string}>}|null} $params Required parameters for request.
     *
     * @return \YandexCheckout\Request\Refunds\CreateRefundResponse
     *
     * @throws \YandexCheckout\Common\Exceptions\ApiException                Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\BadApiRequestException      Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\ForbiddenException          Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\InternalServerError         Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\NotFoundException           Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\ResponseProcessingException Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\TooManyRequestsException    Yandex.Checkout API Exception.
     * @throws \YandexCheckout\Common\Exceptions\UnauthorizedException       Yandex.Checkout API Exception.
     * @throws \Exception                                                    Yandex.Checkout API Exception.
     */
    public function createRefund(array $params)
    {
        return $this->client->createRefund($params);
    }

    /**
     * @param array<string, string>                                               $order_info        Information about order.
     * @param array<string, array<string,  array<string, array<string, string>>>> $return_data       Information about requested return.
     * @param float                                                               $amount            Requested to refund amount of money.
     * @param array<string, string>                                               $processor_params  Payment processor params.
     * @param string                                                              $payment_id        Currently refunded payment id.
     * @param bool                                                                $is_refund_partial Flag that indicates type of this refund.
     *
     * @return array{
     *          amount: array{
     *              currency: string,
     *              value: float
     *          },
     *          payment_id: string,
     *          receipt?: array{
     *              customer: array{email: string, phone: string},
     *              items: list<array{amount: array{currency: string, value: float},
     *                                description: string,
     *                                quantity: float,
     *                                vat_code: string
     *                              }>
     *          }
     *        |null}
     */
    public function createRefundParams(array $order_info, array $return_data, $amount, array $processor_params, $payment_id, $is_refund_partial)
    {
        $params = [
            'payment_id' => $payment_id,
            'amount'     => $this->createPaymentAmount($amount, $processor_params['currency']),
        ];
        if ($is_refund_partial && YesNo::toBool($processor_params['send_receipt'])) {
            $params['receipt'] = $this->receipt_service->getReceiptFromRefund($order_info, $return_data['items']['A'], $processor_params['currency']);
        }
        return $params;
    }
}
