<?php

namespace Tygh\Addons\SberbankFz\Payments;

use Tygh\Common\OperationResult;
use \Tygh\Addons\StorefrontRestApi\Payments\IRedirectionPayment;
use \Tygh\Addons\StorefrontRestApi\Payments\RedirectionPaymentDetailsBuilder;
use Tygh\Payments\Processors\SberbankFz as SberbankCore;
use Tygh\Tools\Url;

class SberbankMobile implements IRedirectionPayment
{
    protected $order_info = array();

    protected $auth_info = array();

    protected $payment_info = array();

    /** @var \Tygh\Addons\StorefrontRestApi\Payments\RedirectionPaymentDetailsBuilder $details_builder */
    protected $details_builder;

    /** @var \Tygh\Common\OperationResult $preparation_result */
    private $preparation_result;

    /**
     * Sber payment constructor.
     */
    public function __construct()
    {
        $this->details_builder = new RedirectionPaymentDetailsBuilder();
        $this->preparation_result = new OperationResult();
    }

    /** @inheritdoc */
    public function setOrderInfo(array $order_info)
    {
        $this->order_info = $order_info;

        return $this;
    }

    /** @inheritdoc */
    public function setAuthInfo(array $auth_info)
    {
        $this->auth_info = $auth_info;

        return $this;
    }

    /** @inheritdoc */
    public function setPaymentInfo(array $payment_info)
    {
        $this->payment_info = $payment_info;

        return $this;
    }

    /** @inheritdoc */
    public function getDetails(array $request)
    {
        $sberbank_response = $this->registerPayment($this->payment_info, $this->order_info);

        $pp_response = array(
            'transaction_id' => $sberbank_response['orderId']
        );

        fn_update_order_payment_info($this->order_info['order_id'], $pp_response);

        $this->preparation_result->setSuccess($this->isUrlReturned($sberbank_response));

        if (isset($sberbank_response['formUrl'])) {
            $payment_link = trim($sberbank_response['formUrl']);

            $this->preparation_result->setData(
                $this->details_builder
                    ->setMethod(RedirectionPaymentDetailsBuilder::GET)
                    ->setPaymentUrl($payment_link)
                    ->setReturnUrl($this->getUrl(
                        array('payment_notification', 'return'),
                        array(
                            'payment'  => 'sberbank_fz',
                            'ordernumber' => $this->order_info['order_id'],
                        )
                    ))
                    ->setCancelUrl($this->getUrl(
                        array('payment_notification', 'error'),
                        array(
                            'payment'  => 'sberbank_fz',
                            'ordernumber' => $this->order_info['order_id'],
                        )
                    ))
                    ->asArray()
            );
        } else {
            $this->preparation_result->setErrors($this->getPaymentRegistrationErrors($sberbank_response));
        }

        return $this->preparation_result;
    }

    /**
     * Gets URL to submit payment request to.
     *
     * @return string URL
     */
    protected function registerPayment($payment_info, $order_info)
    {
        $sberbank = new SberbankCore($payment_info);
        $response = $sberbank->register($order_info, 'https', true);

        if (!empty($this->payment_info['processor_params']['logging']) && $this->payment_info['processor_params']['logging'] == 'Y') {
            SberbankCore::writeLog($response, 'sberbank.log');
        }

        return $response;
    }

    protected function isUrlReturned($response) 
    {
        return (isset($response['formUrl']) && !empty(trim($response['formUrl']))) ;
    }


    protected function getPaymentRegistrationErrors($response) 
    {
        return array($response['errorCode'] => $response['errorMessage']);
    }

    /**
     * Provides full link with schema, domain, path and query string.
     *
     * @param string|array $dispatch     Dispatch string or array with controller, mode, action
     * @param array        $query_params List of query parameters and their values
     *
     * @return string URL
     */
    protected function getUrl($dispatch, $query_params = array())
    {
        return fn_url(Url::buildUrn($dispatch, $query_params), AREA, 'https');
    }
}
