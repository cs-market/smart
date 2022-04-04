<?php

namespace Tygh\Shippings\Services;

use Tygh\Addons\EshopLogistic\Notifications\NotificationsHelper;
use Tygh\Addons\EshopLogistic\Requests\Delivery;
use Tygh\Addons\EshopLogistic\Requests\Search;
use Tygh\Enum\Addons\EshopLogistic\EshopEnum;
use Tygh\Shippings\IService;
use Tygh\Shippings\IPickupService;
use Tygh\Tygh;

/**
 * EshopLogistic shipping service
 */
class EshopLogistic implements IService, IPickupService 
{

    private $shipping_info;
    private $_allow_multithreading = false;
    private $request_data = [];

    
    
    public function prepareData($shipping_info)
    {
        $this->shipping_info = $shipping_info;
    }

    public function processResponse($response)
    {
        $return = [
            'cost' => false,
            'error' => false,
        ];
        
        $rates = $this->_getRates($response);
        
        if (!empty($rates)) {

            $return = array_merge($return, $rates);

        } else {
            $return['error'] = $this->processErrors($response);
        }

        $this->storeShippingData($return);
        
        return $return;
    }

    public function processErrors($response)
    {
        
        return $response;
    }

    public function allowMultithreading()
    {
        return $this->_allow_multithreading;
    }

    public function getRequestData($for_request = false)
    {
        $service_name = !empty($this->shipping_info['shipping']) ? $this->shipping_info['shipping'] : '';
        $origination = $this->shipping_info['package_info']['origination'];
        $location = $this->shipping_info['package_info']['location'];
        $code = $this->shipping_info['service_code'];

        if ($origination['country'] != 'RU' || $location['country'] != "RU") {

            NotificationsHelper::setError(__("eshop_logistic.only_russian_country"));
            return [];
        }

        $city_from  = $this->getCityFias($origination);
        $city_to   = $this->getCityFias($location);
        

        $city_error = false;
        if (empty($city_from)) {
            $city_error = true;
            NotificationsHelper::setError(__("eshop_logistic.can_not_find_city", ["[city]" => $city_from]));
            
        }
        if (empty($city_to)) {
            $city_error = true;
            NotificationsHelper::setError(__("eshop_logistic.can_not_find_city", ["[city]" => $city_to]));
        }
        
        if ($city_error) {
            return [];
        }

        
        $search_city_to_request = new Search($city_to);
        $service_city_to   = $search_city_to_request->getSityForDelivery($code);

        $search_city_from_request = new Search($city_from);
        $service_city_from = $search_city_from_request->getSityForDelivery($code);
        
        $service_city_error = false;
        
        if (empty($service_city_from)) {
            $service_city_error = true;
            NotificationsHelper::setError(__("eshop_logistic.can_not_find_city_for_delivery_service"), ["[city]" => $service_city_from, "[service]" => $service_name]);
        }

        if (empty($service_city_to)) {
            $service_city_error = true;
            NotificationsHelper::setError(__("eshop_logistic.can_not_find_city_for_delivery_service"), ["[city]" => $service_city_from, "[service]" => $service_name]);
        }
        
        if ($service_city_error) {
            
            return [];
        }
        
        $eshop_delivery = new Delivery($code, $service_city_from, $service_city_to, $this->shipping_info);
     
        $this->request_data = $request_data = [
            'method' => 'post',
            'url' => $eshop_delivery->getApiUrl(),
            'data' => $eshop_delivery->getApiData(),
            'service_class' => $eshop_delivery,
        ];
        
        if (!$for_request) {
            if (!empty($request_data['data'])) {
                $eshop_delivery->finishLog(__('eshop_logistic.dilevery.success_get_request_data'));
            }else {
                $eshop_delivery->finishLog(__('eshop_logistic.dilevery.failed_get_request_data'), true);
            }
            
        }
        
        return $request_data;
    }

    public function getSimpleRates()
    {
        $data = $this->getRequestData(true);
        
        if (!empty($data)) {

            $eshop_delivery = $data['service_class'];
            
            $response = $eshop_delivery->request();
            
            return $response;
        }
        
        return [];
        
    }

    public function getPickupMinCost()
    {
        $shipping_data = $this->getStoredShippingData();
        return isset($shipping_data['cost']) ? $shipping_data['cost'] : false;
    }
    
    protected function storeShippingData($data)
    {
        $group_key = isset($this->shipping_info['keys']['group_key']) ? $this->shipping_info['keys']['group_key'] : 0;
        $shipping_id = isset($this->shipping_info['keys']['shipping_id']) ? $this->shipping_info['keys']['shipping_id'] : 0;
        
        if (isset($group_key) && isset($shipping_id)) {
            Tygh::$app['session']['cart']['shippings_extra']['data']['eshop'][$group_key][$shipping_id] = $data;
        }
        
        return true;
    }

    protected function getStoredShippingData()
    {
        $group_key = isset($this->shipping_info['keys']['group_key']) ? $this->shipping_info['keys']['group_key'] : 0;
        $shipping_id = isset($this->shipping_info['keys']['shipping_id']) ? $this->shipping_info['keys']['shipping_id'] : 0;
        
        if (isset(Tygh::$app['session']['cart']['shippings_extra']['data']['eshop'][$group_key][$shipping_id])) {
            return Tygh::$app['session']['cart']['shippings_extra']['data']['eshop'][$group_key][$shipping_id];
        }

        return [];
    }

    public function getPickupPoints()
    {
        $shipping_data = $this->getStoredShippingData();
        return isset($shipping_data[EshopEnum::TERMINALS]) ? $shipping_data[EshopEnum::TERMINALS] : false;
    }


    public function getPickupPointsQuantity()
    {
        $shipping_data = $this->getStoredShippingData();
        return isset($shipping_data[EshopEnum::TERMINALS]) ? count($shipping_data[EshopEnum::TERMINALS]) : false;
    }

    private function getCityFias($address_data)
    {
        $city_fias = '';

        if (!empty($address_data['city']) && !empty($address_data['country']) && !empty($address_data['state'])) {

            $cities_ids = fn_rus_cities_get_city_ids($address_data['city'], $address_data['state'],$address_data['country']);
            
            if (empty($cities_ids) || count($cities_ids) != 1) {
                return '';
            }
    
            $city_id = reset($cities_ids);
            
            $city_fias = db_get_field("SELECT city_fias FROM ?:rus_cities WHERE city_id = ?i", $city_id);            
        }

        return $city_fias;
    }

    private function _getRates($response)
    {   
        $eshop_services_info = fn_get_session_data('eshop_services_info');
        $rates = [];
        
        if ($this->avaliForSelectedPayment($this->shipping_info, $eshop_services_info)) {
            
            $rates = [
                'cost' => isset($response['price']) ? $response['price'] : false,
                'delivery_time' => !empty($response['time']) ? $response['time'] : false,
                'comment' => !empty($response['comment']) ? $response['comment'] : false,
            ];

            if (!empty($response['terminals'])) {
                
                $terminals = (array) $response['terminals'];

                foreach ($terminals as &$terminal) {
                    $terminal = (array) $terminal;
                }
                $rates['terminals'] = $terminals;
            }
        }
        
        
        return $rates;
    }

    private function avaliForSelectedPayment($shipping_info, $eshop_services_info)
    {   
        $selected_payment = !empty($this->request_data['data']['payment']) ? $this->request_data['data']['payment'] : false; 
        
        if (empty($selected_payment) && AREA != 'C') {
            return true;
        }elseif (empty($selected_payment) && AREA == 'C') {

            $payment_methods = fn_prepare_checkout_payment_methods($cart, $auth, CART_LANGUAGE);
            $payment_list = fn_checkout_flatten_payments_list($payment_methods);
            
            $selected_payment = fn_eshop_logistic_get_eshop_payment_type_by_code(reset($payment_list)['eshop_payment_type']);
        }

        $avail_payments = [];
        
        if (!empty($eshop_services_info)) {
            foreach ($eshop_services_info as $eshop_service_key => $eshop_service) {
                if (strpos($shipping_info['service_code'], $eshop_service_key) !== false) {
                    if (!empty($eshop_service['payments'])) {
                        foreach ($eshop_service['payments'] as $eshop_payment) {
                            $avail_payments[] = $eshop_payment['key'];
                        }
                    }
                }
            }
        }
        
        return in_array($selected_payment, $avail_payments);

    }
}