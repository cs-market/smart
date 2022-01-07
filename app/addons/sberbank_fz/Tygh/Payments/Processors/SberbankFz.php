<?php

namespace Tygh\Payments\Processors;

use Tygh\Registry;
use Tygh\Http;
use Tygh\Payments\SberDiscountHelper;

class SberbankFz
{
    protected $PROD_URL = 'https://securepayments.sberbank.ru/';
    protected $TEST_URL = 'https://3dsec.sberbank.ru/';
    protected $_payment_method = 'regular';
    protected $_two_staging = false;
    protected $_logging = false;
    protected $_send_order = false;
    protected $_tax_system = 0;
    protected $_tax_type = 0;

    protected $_ffd_version;
    protected $_ffd_paymentMethodType;
    protected $_ffd_paymentObjectType;

    public function __construct($processor_data)
    {
        $this->_login = $processor_data['processor_params']['login'];
        $this->_password = $processor_data['processor_params']['password'];

        // previous versions support
        if ($processor_data['processor_params']['mode'] == 'test' || $processor_data['processor_params']['mode'] == 'dev') {
            $this->_url = $this->TEST_URL;
        } else {
            $this->_url = $this->PROD_URL;
        }

        if (!empty($processor_data['processor_params']['send_order']) && $processor_data['processor_params']['send_order'] == 'Y') {
            $this->_send_order = true;
        }

        $this->_payment_method = ($this->_send_order) ? $processor_data['processor_params']['payment_method'] : 'regular';

        if ($this->_payment_method == 'credit' || $this->_payment_method == 'installment') {
            $this->_url = $this->_url . 'sbercredit/';
            $processor_data['processor_params']['two_staging'] = 0;
        } else {
            $this->_url = $this->_url . 'payment/rest/';
        }

        if (!empty($processor_data['processor_params']['two_staging'])) {
            $this->_two_staging = true;
        }

        $this->_tax_system = (!empty($processor_data['processor_params']['tax_system'])) ? $processor_data['processor_params']['tax_system'] : 0;

        if (!empty($processor_data['processor_params']['logging']) && $processor_data['processor_params']['logging'] == 'Y') {
            $this->_logging = true;
        }

        $this->_tax_type = (!empty($processor_data['processor_params']['tax_type'])) ? $processor_data['processor_params']['tax_type'] : 0;
        $this->_ffd_version = (!empty($processor_data['processor_params']['ffd_version'])) ? $processor_data['processor_params']['ffd_version'] : "v10";
        $this->_ffd_paymentMethodType = (!empty($processor_data['processor_params']['ffd_paymentMethodType'])) ? $processor_data['processor_params']['ffd_paymentMethodType'] : 1;
        $this->_ffd_paymentObjectType = (!empty($processor_data['processor_params']['ffd_paymentObjectType'])) ? $processor_data['processor_params']['ffd_paymentObjectType'] : 1;
    }

    public function register($order_info, $protocol = 'current', $is_mobile = false)
    {
        ini_set('serialize_precision', 10);
        $order_total = $this->convertSum($order_info['total']);

        $order_id = $order_info['order_id'];
        $orderNumber = $order_id . '_' . substr(md5($order_id . TIME), 0, 3);
        $mobile_postfix = $is_mobile ? '&isMobilePayment=1' : '';
        $data = array(
            'userName' => $this->_login,
            'password' => $this->_password,
            'orderNumber' => $orderNumber,
            'amount' => $order_total * 100,
            'returnUrl' => fn_url("payment_notification.return?payment=sberbank_fz&ordernumber=$order_id" . $mobile_postfix, AREA, $protocol),
            'failUrl' => fn_url("payment_notification.error?payment=sberbank_fz&ordernumber=$order_id" . $mobile_postfix, AREA, $protocol),
            'jsonParams' => json_encode(
                [
                    'CMS:' => 'CS-Cart 4.12.x',
                    'Module-Version: ' =>  'CS-Market.com new version with fz-54 and mobile application support',
                    'phone' => preg_replace('/\D+/', '', $order_info['phone'])
                ]
            ),
        );

        $iso_currencies = fn_get_schema('sberbank', 'currencies');
        if (isset($iso_currencies[CART_PRIMARY_CURRENCY])) {
            $data['currency'] = $iso_currencies[CART_PRIMARY_CURRENCY];
        }
        $data['description'] = __('order') . ' #' . $order_id;
        $data['language'] = DESCR_SL;

        if ($this->_send_order) {
            $product_taxes = array();

            foreach($order_info['taxes'] as $k => $v) {
                $item_rate_value = (int)$v['rate_value'];

                foreach ($v['applies']['items']['P'] as $c => $d) {

                    if ($item_rate_value == 20) {
                        $tax_type = 6;
                    } else if ($item_rate_value == 18) {
                        $tax_type = 3;
                    } else if ($item_rate_value == 10) {
                        $tax_type = 2;
                    } else if ($item_rate_value == 0) {
                        $tax_type = 1;
                    } else {
                        $tax_type = $this->_tax_type;
                    }
                    $product_taxes[$c] = $tax_type;
                }
            }

            $data['taxSystem'] = $this->_tax_system;

            $items = array();
            $itemsCnt = 1;

            $subtotal_discount = isset($order_info['subtotal_discount']) ? $order_info['subtotal_discount'] : 0;
            $shipping_cost = isset($order_info['shipping_cost']) ? $order_info['shipping_cost'] : 0;

            $order_total = 0;

            /* Заполнение массива данных корзины */
            foreach ($order_info['products'] as $value) {

                $q = isset($value['amount']) ? $value['amount'] : 1;
                $p = isset($value['price']) ? $value['price'] * 100 : 0;

                $tax_type = (!empty($product_taxes)) ? $product_taxes[$value['item_id']] : 0;

                $item['positionId'] = $itemsCnt++;
                $item['name'] = isset($value['product']) ? strip_tags($value['product']) : '';
                $item['quantity'] = array(
                    'value' => $q,
                    'measure' => 'шт.'
                );
                $item['itemAmount'] = $p * $q;
                $item['itemCode'] = $value['product_code'];
                $item['tax'] = array(
                    'taxType' => $tax_type ?? 0
                );
                $item['itemPrice'] = $p;
                $order_total += $item['itemAmount'];

                // FFD 1.05 added
                if ($this->_ffd_version == 'v105') {

                    $attributes = array();
                    $attributes[] = array(
                        "name" => "paymentMethod",
                        "value" => $this->_ffd_paymentMethodType
                    );
                    $attributes[] = array(
                        "name" => "paymentObject",
                        "value" => $this->_ffd_paymentObjectType
                    );
                    $item['itemAttributes'] = ($this->_payment_method == 'regular') ? ['attributes' => $attributes] : $attributes;
                }

                fn_set_hook('sberbank_edit_item', $item, $value, $order_info);

                $items[] = $item;
            }

            // DISCOUNT_VALUE_SECTION
            if ($subtotal_discount > 0) {
                $new_order_total = 0;
                foreach ($items as &$i) {
                    $p_discount = round($i['itemAmount']  / $order_total * $subtotal_discount, 2) * 100;
                    self::correctBundleItem($i, $p_discount);
                    // $i['discount']['discountType'] = 'summ';
                    // $i['discount']['discountValue'] += $p_discount;
                    $new_order_total += $i['itemAmount'];
                }
                $data['amount'] = $new_order_total;
            }

            // DELIVERY
            if ($shipping_cost > 0) {
                $itemShipment['positionId'] = $itemsCnt;
                $itemShipment['name'] = 'Доставка';
                $itemShipment['quantity'] = array(
                    'value' => 1,
                    'measure' => 'piece'
                );
                $itemShipment['itemAmount'] = $itemShipment['itemPrice'] = $shipping_cost * 100;
                $itemShipment['itemCode'] = 'Delivery';
                $itemShipment['tax'] = array(
                    'taxType' => $tax_type ?? 0
                );

                // FFD 1.05 added
                if ($this->_ffd_version == 'v105') {
                    $attributes = array();
                    $attributes[] = array(
                        "name" => "paymentMethod",
                        "value" => $this->_ffd_paymentMethodType
                    );
                    $attributes[] = array(
                        "name" => "paymentObject",
                        "value" => 4
                    );
                    $itemShipment['itemAttributes'] = ($this->_payment_method == 'regular') ? ['attributes' => $attributes] : $attributes;
                }

                //$data['amount'] += $shipping_cost * 100;
                $items[] = $itemShipment;
            }

            $order_bundle = array(
                'orderCreationDate' => date("Y-m-d\TH:i:s", $order_info['timestamp']),
                'customerDetails' => array(
                    //'email' => $order_info['email'],
                    'phone' => preg_replace('/\D+/', '', $order_info['phone'])
                ),
                'cartItems' => array('items' => $items)
            );

            // DISCOUNT CALCULATE

            $discountHelper = new SberDiscountHelper();

            $discount = $discountHelper->discoverDiscount($data['amount'], $order_bundle['cartItems']['items']);
            if ($discount > 0) {
                $discountHelper->setOrderDiscount($discount);
                $recalculatedPositions = $discountHelper->normalizeItems($order_bundle['cartItems']['items']);
                $order_bundle['cartItems']['items'] = $recalculatedPositions;
            }

            if ($this->_payment_method != 'regular') {
                $data['dummy'] = 'true';
                $order_bundle['installments'] = array(
                    'productID' => '10',
                    'productType' => strtoupper($this->_payment_method)
                );
            }
            $data['orderBundle'] = json_encode($order_bundle);
        }

        $action_adr = 'register.do';
        if ($this->_two_staging) {
            $action_adr = 'registerPreAuth.do';
        }

        $this->_response = Http::post($this->_url . $action_adr, $data);

        if ($this->_logging) {
            self::writeLog($data, 'sberbank_register_order.log');
        }

        $this->_response = json_decode($this->_response, true);

        if (!empty($this->_response['errorCode'])) {
            $this->_error_code = $this->_response['errorCode'];
            $this->_error_text = $this->_response['errorMessage'];
        }

        return $this->_response;
    }

    public function correctBundleItem(&$item, $discount) {

        $item['itemAmount'] -= $discount;
        $item['itemPrice'] = $item['itemAmount'] % $item['quantity']['value'];
        if ($item['itemPrice'] != 0)  {
            $item['itemAmount'] += $item['quantity']['value'] - $item['itemPrice'];
        };

        $item['itemPrice'] = $item['itemAmount'] / $item['quantity']['value'];

        return $item;
    }

    public static function getPaymentResult($order_id, $transaction_id = false) {
        $pp_response = array();

        $order_info = fn_get_order_info($order_id);
        if (!empty($order_info)) {
            if ($transaction_id && $order_info['payment_info']['transaction_id'] != $transaction_id) {
                $pp_response = array(
                    'order_status' => 'F',
                    'reason_text' => __("addons.rus_sberbank.wrong_transaction_id"),
                );
            } else {
                $processor_data = fn_get_processor_data($order_info['payment_id']);
                $sberbank = new SberbankFz($processor_data);
                $response = $sberbank->getOrderExtended($order_info['payment_info']['transaction_id']);

                if ($sberbank->isError()) {
                    $pp_response = array(
                        'order_status' => 'F',
                        'reason_text' => $response['errorMessage']
                    );

                } elseif (in_array($response['orderStatus'], [1,2]) ) {
                    if ($response['amount'] == round($order_info['total'] * 100)) {
                        $pp_response = array(
                            'order_status'    => $processor_data['processor_params']['confirmed_order_status'],
                            'card_number'     => $response['cardAuthInfo']['pan'],
                            'cardholder_name' => $response['cardAuthInfo']['cardholderName'],
                            'expiry_month'    => substr($response['cardAuthInfo']['expiration'], 0, 4),
                            'expiry_year'     => substr($response['cardAuthInfo']['expiration'], 0, -2),
                            'bank'            => $response['bankInfo']['bankName'],
                            'ip_address'      => $response['ip'],
                        );
                    } else {
                        $pp_response['reason_text'] = __("addons.rus_sberbank.wrong_amount");
                    }
                } elseif (!empty(trim($response['actionCodeDescription']))) {
                    $pp_response = array(
                        'order_status' => 'F',
                        'reason_text' => $response['actionCodeDescription'],
                        'ip_address' => $response['ip'],
                    );
                }
            }
            if (!empty($processor_data['processor_params']['logging']) && $processor_data['processor_params']['logging'] == 'Y') {
                SberbankFz::writeLog(['dispatch' => Registry::get('runtime.controller') . '.' . Registry::get('runtime.mode'), 'order_id' => $order_id, 'transaction_id' => $order_info['payment_info']['transaction_id'], 'payment' => 'sberbank_fz', 'message' => $pp_response['reason_text']], 'sberbank_request.log');
            }
            if ($pp_response) {
                fn_finish_payment($order_id, $pp_response);
            }
        }
    }

    public function getOrderExtended($transaction_id)
    {
        $data = array(
            'userName' => $this->_login,
            'password' => $this->_password,
            'orderId' => $transaction_id
        );

        $this->_response = Http::post($this->_url . 'getOrderStatusExtended.do', $data);

        $this->_response = json_decode($this->_response, true);

        if (!empty($this->_response['errorCode'])) {
            $this->_error_code = $this->_response['errorCode'];
            $this->_error_text = $this->_response['errorMessage'];
        }

        return $this->_response;
    }

    public function getErrorCode()
    {
        return $this->_error_code;
    }

    public function getErrorText()
    {
        return $this->_error_text;
    }

    public function isError()
    {
        return !empty($this->_error_code);
    }

    public static function writeLog($data, $file = 'sberbank.log')
    {
        $path = fn_get_files_dir_path();
        fn_mkdir($path);
        $file = fopen($path . $file, 'a');

        if (!empty($file)) {
            fputs($file, 'TIME: ' . date('Y-m-d H:i:s', TIME) . "\n");
            fputs($file, fn_array2code_string($data) . "\n\n");
            fclose($file);
        }
    }

    public static function convertSum($price)
    {
        if (CART_PRIMARY_CURRENCY != 'RUB') {
            $price = fn_format_price_by_currency($price, CART_PRIMARY_CURRENCY, 'RUB');
        }

        $price = fn_format_rate_value($price, 'F', 2, '.', '', '');

        return $price;
    }
}
