<?php

namespace Tygh\Addons\EshopLogistic\Requests;

use Tygh\Addons\EshopLogistic\Logger\Logger;
use Tygh\Addons\EshopLogistic\Notifications\NotificationsHelper;
use Tygh\Http;
use Tygh\Registry;

class Request
{
    protected $apiUrl = "https://api.eshoplogistic.ru/";
    protected $apiKey;
    protected $apiPath;
    protected $apiParams = [];
    protected $logger;
    protected $error_code = 400;
    protected $caching = false;
    protected $cache_key = '';
    protected $cache_lifetime;
    protected $cache_group_key = "eshop_logistic";

    function __construct()
    {   
        $this->logger = new Logger();
        $this->setApiKey();
        $this->setApiPath();

        if (!empty($this->apiKey)) {
            $this->setParams(['key' => $this->apiKey]);
        }
        
        $this->setCacheLifetime();
        
    }

    protected function setApiKey()
    {
        $this->apiKey = Registry::ifGet("addons.eshop_logistic.eshop_api_key", '');
    }

    protected function setCacheLifetime()
    {
        $this->cache_lifetime = Registry::ifGet("addons.eshop_logistic.eshop_cache_lifetime", 0) * SECONDS_IN_HOUR;
    }

    protected function setParams($params)
    {
        $this->apiParams = array_merge($this->apiParams, $params);
    }


    protected function setApiPath()
    {
        $this->apiUrl .= $this->apiPath;
    }

    protected function setLoggerType($type)
    {   
        $this->logger->setType($type);
    }

    public function request()
    {
        if (empty($this->apiKey)) {
            
            NotificationsHelper::setError(__("eshop_logistic.api_key_not_found"));
            return false;
        }

        
        $this->beforeRequest();
        
        if ($this->caching && !empty($this->cache_key) && Registry::get('addons.eshop_logistic.eshop_use_cache') == 'Y') {

            $key = $this->cache_key;
            $caching_response = fn_eshop_logistic_get_session_data($key, $this->cache_group_key);
        }

        if (!empty($caching_response)) {

            $response = $caching_response;
            $this->logger->isCaching();
            
        }else {

            $response = json_decode(Http::post($this->apiUrl, $this->apiParams));
            
            if ($this->caching && !empty($this->cache_key)) {
                fn_eshop_logistic_set_session_data($this->cache_key, $this->cache_group_key, $response, $this->cache_lifetime);
            }
        }
        
        
        $response = $this->parseResponse($response);

        return $response;

    }

    protected function beforeRequest()
    {

    }
    
    protected function parseResponse($response)
    {
        $_message = !empty($response->msg) ? $response->msg : '';
        $message = '';

        if (is_object($_message)) {
            foreach ($_message as $key_msg  => $msg) {
                $message .= $key_msg . " - " . $msg . "</br>";
            }
        }

        if ($response->status >= $this->error_code) {
            $message .= " Error:" . $response->status;
        }
        
        $data = !empty($response->data) ? serialize($response->data) : 
                (!empty($response->errors) ? serialize($response->errors) : '');
        
        $this->logger->setMessAndData($message, $data);

        
        if (!is_object($response) 
            || empty($response->status) 
            || $response->status >= $this->error_code
            || $response->success !== true) {
            
            if (!empty($this->service_code) && !empty($this->service_method)) {
                $this->logger->setMessAndData('</br>Service - ' . $this->service_code . '. Method - ' .$this->service_method . '</br>');
            }

            $this->logger->setError();
            $this->logger->finishLog();
            
            NotificationsHelper::setError(__("eshop_logistic.api_request_fail"));
            return false;
        }

        $response = $this->processResponse($response);
        
        $this->logger->finishLog();

        return $response;
    }

    protected function processResponse($response)
    {
        return $response;
    }

    protected function enableCache()
    {
        $this->caching = true;
    }
}