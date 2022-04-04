<?php

namespace Tygh\Addons\EshopLogistic\Requests;

use Tygh\Addons\EshopLogistic\Notifications\NotificationsHelper;
use Tygh\Enum\Addons\EshopLogistic\EshopEnum;
use Tygh\Enum\Addons\EshopLogistic\LoggerEnum;

class Search extends Request
{
    protected $apiPath = 'api/search';
    protected $logger_type;
    protected $cache_key_prefix = 'eshop_services_search';
    protected $city_fias;


    function __construct($city)
    {
        parent::__construct();
        
        $this->enableCache();
        $this->setLoggerType(LoggerEnum::CITIES_SEARCH_REQUEST);
        $this->city_fias = $city;   
    }

    public function getSityForDelivery($service_code)
    {
        list($_service_code, ) = explode('_', $service_code);

        if ($_service_code == EshopEnum::CUSTOM_DELIVERY) {
            $message = __('eshop_logistic.custom_delivery_city_request'); 
            
            $this->logger->setMessAndData($message);
            $this->logger->finishLog();
            return $this->city_fias;
        }

        $has_error = false;
        $this->generateCacheKey($this->city_fias);
        $this->setParams(['target' => $this->city_fias]);
        $response = $this->request();

        if (empty($response->data) || count($response->data) > 1) {

            $has_error = true;
        }

        if ($has_error) {
            $this->logger->setError();
            
            $error = __('eshop_logistic.can_not_find_city_for_delivery_service'); 
            
            $this->logger->setMessAndData($error);
            $this->logger->finishLog();
            NotificationsHelper::setError($error);
        }else {
            $response_data = current($response->data);
        }
        
        $service_city = !empty($response_data->services->$_service_code) ? $response_data->services->$_service_code : '';
        
        
        return $service_city;
    }

    private function generateCacheKey($key)
    {
        $this->cache_key = $this->cache_key_prefix . '_' . $key;
    }
    
}
