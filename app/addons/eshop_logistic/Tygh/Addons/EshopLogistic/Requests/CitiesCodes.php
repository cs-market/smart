<?php

namespace Tygh\Addons\EshopLogistic\Requests;

use Tygh\Addons\EshopLogistic\Logger\Logger;
use Tygh\Addons\EshopLogistic\Notifications\NotificationsHelper;
use Tygh\Addons\EshopLogistic\Parsers\CitiesParser;
use Tygh\Enum\Addons\EshopLogistic\LoggerEnum;
use Tygh\Http;

class CitiesCodes extends Request
{
    protected $apiPath = 'public/cities2.csv';
    private $cities_filepath = '/eshop_logistic/cities/';
    private $page;
    

    function __construct()
    {
        $this->logger = new Logger();
        $this->setLoggerType(LoggerEnum::CITIES_CODES_REQUEST);
        $this->setApiPath();
        
    }

    public function request($page = 0)
    {
        if (!empty($page)) {

            $cities_dir = fn_get_files_dir_path() . $this->cities_filepath;
            $filename = $cities_dir . 'cities.csv';
            $cities_parser = new CitiesParser($filename, $this->logger);
            $cities_parser->parseCsv($page);

        }else{
            $response = Http::get($this->apiUrl, $this->apiParams);
    
            $this->parseResponse($response);
        }
    }

    protected function parseResponse($response)
    {
        $decode_response = json_decode($response);

        if (empty($response) || !empty($decode_response->error)) {
            
            
            $this->logger->setError();
            $this->logger->finishLog();

            $error = !empty($response->error) ? $response->error : __("eshop_logistic.internal_error"); 
            
            $this->logger->setMessAndData($error);

            NotificationsHelper::setError($error);
            
            return false;
        }

        $this->processResponse($response);
        
        $this->logger->finishLog();

        return $response;
    }

    protected function processResponse($response)
    {
        $cities_dir = fn_get_files_dir_path() . $this->cities_filepath;
        fn_mkdir($cities_dir);

        $filename = $cities_dir . 'cities.csv';

        $save_result = file_put_contents($filename, $response);

        if (empty($save_result)) {
            NotificationsHelper::setWarning(__("eshop_logistic.can_not_save_cities_file"));
            return false;
        }
        $cities_parser = new CitiesParser($filename, $this->logger);
        $cities_parser->parseCsv(); 

        return $response;
    }
}
