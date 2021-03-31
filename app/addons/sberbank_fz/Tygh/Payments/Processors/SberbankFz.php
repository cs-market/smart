<?php

namespace Tygh\Payments\Processors;

use Tygh\Registry;
use Tygh\Http;
use Tygh\Payments\Processors\SberDiscountHelper;

class SberbankFz extends Sberbank
{
    protected $PROD_URL = 'https://securepayments.sberbank.ru/payment/rest/';
    protected $TEST_URL = 'https://3dsec.sberbank.ru/payment/rest/';

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

        if (!empty($processor_data['processor_params']['two_staging'])) {
            $this->_two_staging = true;
        }

        if (!empty($processor_data['processor_params']['send_order']) && $processor_data['processor_params']['send_order'] == 'Y') {
            $this->_send_order = true;
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

    public function register($order_info, $protocol = 'current')
    {
        $order_total = $this->convertSum($order_info['total']);

        $order_id = $order_info['order_id'];
        $orderNumber = $order_id . '_' . substr(md5($order_id . TIME), 0, 3);

        $data = array(
            'userName' => $this->_login,
            'password' => $this->_password,
            'orderNumber' => $orderNumber,
            'amount' => $order_total * 100,
            'returnUrl' => fn_url("payment_notification.return?payment=sberbank_fz&ordernumber=$order_id", AREA, $protocol),
            'failUrl' => fn_url("payment_notification.error?payment=sberbank_fz&ordernumber=$order_id", AREA, $protocol),
            'jsonParams' => json_encode(
                [
                    'CMS:' => 'cs-cart 4.12.x',
                    'Module-Version: ' =>  'CS-Market.com new version with fz-54 and mobile application support'
                ]
            ),
        );

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
                    'measure' => 'piece'
                );
                $item['itemAmount'] = $p * $q;
                $item['itemCode'] = $value['product_code'];
                $item['tax'] = array(
                    'taxType' => $tax_type
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
                    $item['itemAttributes']['attributes'] = $attributes;
                }

                $items[] = $item;
            }

            $data['amount'] = $order_total;

//             DISCOUNT_VALUE_SECTION
            if ($subtotal_discount > 0) {
                $new_order_total = 0;
                foreach ($items as &$i) {
                    $p_discount = round($i['itemAmount']  / $order_total * $subtotal_discount, 2) * 100;
                    self::correctBundleItem($i, $p_discount);
//                    $i['discount']['discountType'] = 'summ';
//                    $i['discount']['discountValue'] += $p_discount;
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
                    'taxType' => $tax_type
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
                    $itemShipment['itemAttributes']['attributes'] = $attributes;
                }

                $data['amount'] += $shipping_cost * 100;
                $items[] = $itemShipment;
            }

            $order_bundle = array(
                'orderCreationDate' => time(),
                'customerDetails' => array(
                    'email' => $order_info['email'],
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

            $data['orderBundle'] = json_encode($order_bundle);
        }

        $action_adr = 'register.do';
        if ($this->_two_staging) {
            $action_adr = 'registerPreAuth.do';
        }

        $this->_response = Http::post($this->_url . $action_adr, $data);

        if ($this->_logging) {
            self::writeLog($data, 'Register Order');
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
}
