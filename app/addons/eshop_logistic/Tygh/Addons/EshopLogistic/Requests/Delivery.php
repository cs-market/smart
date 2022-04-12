<?php

namespace Tygh\Addons\EshopLogistic\Requests;

use Tygh\Enum\Addons\EshopLogistic\EshopEnum;
use Tygh\Enum\Addons\EshopLogistic\LoggerEnum;
use Tygh\Registry;
use Tygh\Shippings\Shippings;
use Tygh\Tygh;

class Delivery extends Request
{
    protected $apiPath = 'api/delivery/[service_code]';
    protected $logger_type;
    protected $cache_key;
    protected $delivery_services_info;
    protected $service_code;
    protected $service_method;
    protected $services_info_session_key = 'eshop_services_info';
    

    function __construct($service_code, $city_from, $city_to, $shipping_info)
    {
        parent::__construct();
       
        
        $this->enableCache();

        list($this->service_code, $this->service_method) = explode('_', $service_code);

        $this->setServiceToApiUrl($this->service_code);
        $this->setLoggerType(LoggerEnum::DELIVERY_REQUEST);

        $this->setParams($this->getDeliveryParams($city_from, $city_to, $shipping_info));
        $this->generateCacheKey();
        
    }

    protected function beforeRequest()
    {   
        if (Registry::get('addons.eshop_logistic.eshop_use_cache') == 'Y') {
            $saved_eshop_servises_info = fn_get_session_data($this->services_info_session_key);
        }
        

        if (empty($saved_eshop_servises_info)) {

            $init_request = new Init();
            $this->delivery_services_info = $init_request->request();

            if (!empty($this->delivery_services_info->data)) {
                $shipping_services = (array) $this->delivery_services_info->data;
    
                foreach ($shipping_services as &$service) {
                    $service = (array) $service;

                    if (!empty($service['payments'])) {
                        $service['payments'] = (array) $service['payments'];
                        
                        foreach ($service['payments'] as &$payment_method) {
                            $payment_method = (array) $payment_method;
                        }
                    }
                }
            
                fn_set_session_data($this->services_info_session_key, $shipping_services);    
            }
        }
    }

    protected function processResponse($response) 
    {
        
        $delivery_response = [];
        $service_method = $this->service_method;
        
        $this->logger->setMessAndData('Service - ' . $this->service_code . '. Method - ' .$this->service_method . '</br>');

        if (!empty($response->data->$service_method)) {

            $delivery_response = (array) $response->data->$service_method;
        }
    
        
        if ($service_method == EshopEnum::TERMINAL) {
            
            $terminals = EshopEnum::TERMINALS;
            
            $delivery_response[$terminals] = !empty($response->data->$terminals) ? $response->data->$terminals : [];
        }
        
        return $delivery_response;
    }
    

    public function getApiData()
    {
        return $this->apiParams;
    }

    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    public function finishLog($message, $is_error = false) 
    {
        $this->setLoggerType(LoggerEnum::GET_DATA_FOR_DELIVERY);
        $this->logger->setMessAndData($message);

        if ($is_error) {
            $this->logger->setError();
        }

        $this->logger->finishLog();
    }

    private function setServiceToApiUrl($service_code) 
    {
        $this->apiUrl = str_replace('[service_code]', $service_code, $this->apiUrl);
    }

    private function getDeliveryParams($city_from, $city_to, $shipping_info)
    {
        
        $params = [
            'from'      => $city_from,
            'to'        => $city_to,
            'payment'   => $this->getPaymentMethod(),
        ];
        
        $offers = $this->getOffers($shipping_info);
        
        if (!empty($offers)) {
            $params['offers'] = $offers;
            
        }else {
            $params['weight'] = $shipping_info['package_info']['W'];
        }
        
        return $params;
    }

    private function getOffers($shipping_info)
    {
        $offers = [];
        $product_groups = !empty(Tygh::$app['session']['cart']['product_groups']) ? Tygh::$app['session']['cart']['product_groups'] : [];

        if (!empty($product_groups)) {

            $current_company_id = !empty($shipping_info['keys']['company_id']) ? $shipping_info['keys']['company_id'] : false;
            $current_group_key = !empty($shipping_info['keys']['group_key']) ? $shipping_info['keys']['group_key'] : false;


            if (!empty($product_groups[$current_group_key]['products'])) {
                
                foreach ($product_groups[$current_group_key]['products'] as $product_data) {
                    if ($product_data['company_id'] == '1815') $product_data['weight'] = $product_data['weight'] / 1000;
                    if ((Shippings::isFreeShipping($shipping_info) && $product_data['free_shipping'] != 'Y') || 
                        (!Shippings::isFreeShipping($shipping_info))) {                        
                        $offers[] = [
                            'article' => !empty($product_data['product_code']) ? $product_data['product_code'] : $product_data['product_id'],
                            'name' => !empty($product_data['product']) ? $product_data['product'] : '',
                            'count' => !empty($product_data['amount']) ? $product_data['amount'] : 0,
                            'price' => !empty($product_data['price']) ? $product_data['price'] : 0,
                            'weight' => !empty($product_data['weight']) ? $product_data['weight'] : 0.001,
                            'dimensions' => $this->getDimensions($product_data),
                        ];
                    }
                }
                
            }
        }
        
        return !empty($offers) ? json_encode($offers) : $offers;
    }

    private function getPaymentMethod()
    {
        $cart = Tygh::$app['session']['cart'];
	    if(!isset($auth))
		    $auth = Tygh::$app['session']['auth'];

        $payment_method = !empty($cart['payment_method_data']['eshop_changed_payment']) ? $cart['payment_method_data']['eshop_changed_payment'] : 
            (!empty($cart['payment_method_data']['eshop_payment_type']) ? $cart['payment_method_data']['eshop_payment_type'] : false);

        if (empty($payment_method) && AREA == 'C') {
            $payment_list = fn_prepare_checkout_payment_methods($cart, $auth, CART_LANGUAGE, false);
            $payment_method = reset($payment_list)['eshop_payment_type'];
        }    
        
            

        return fn_eshop_logistic_get_eshop_payment_type_by_code($payment_method);
    }

    private function generateCacheKey()
    {
        $params = $this->getApiData();
        $params['service'] = $this->apiUrl;
        
        $this->cache_key = md5(serialize($params));
    }

    private function getDimensions($product_data)
    {
        $default_dimensions = "0*0*0";

        if (!empty($product_data['shipping_params'])) {
            foreach ($product_data['shipping_params'] as $param_name => $param) {
                if ($param_name == 'box_height' || $param_name == 'box_length' || $param_name == 'box_width') {
                    $pieces[] = $param;
                }
                
            }
        }

        return !empty($pieces) ? implode('*', $pieces) : $default_dimensions;

    }
}