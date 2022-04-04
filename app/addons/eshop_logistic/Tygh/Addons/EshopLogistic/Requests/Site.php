<?php

namespace Tygh\Addons\EshopLogistic\Requests;

use Tygh\Addons\EshopLogistic\Parsers\ServicesParser;
use Tygh\Enum\Addons\EshopLogistic\LoggerEnum;

class Site extends Request
{
    protected $apiPath = 'api/site';
    protected $logger_type;
    private $settings_objects_name = 'eshop_logistic_account_info';

    function __construct()
    {
        parent::__construct();
        
        $this->setLoggerType(LoggerEnum::SITE_REQUEST);
        
    }

    protected function processResponse($response)
    {
        $response_data  = !empty($response->data) ? (array) $response->data : [];

        /** REMOVE ME DOSTAVISTA */
        if (!empty($response_data['services']->dostavista)) {
            unset($response_data['services']->dostavista);
        }
        /** REMOVE ME DOSTAVISTA */
        
        $data = [
            'name' => $this->settings_objects_name,
            'value' => !empty($response_data) ? serialize(array_merge($response_data, ['time_of_request' => time()])) : '',
        ];
        
        $object_id = db_get_field('SELECT object_id FROM ?:settings_objects WHERE name = ?s', $this->settings_objects_name);

        if (!empty($object_id)) {
            db_query('UPDATE ?:settings_objects SET value = ?s WHERE object_id = ?i', $data['value'], $object_id);
        }else {
            db_query('REPLACE INTO ?:settings_objects ?e', $data);
        }

        if (!empty($response_data['services'])) {

            if (!is_array($response_data['services'])) {
                $response_data['services'] = (array) $response_data['services'];
            }

            $services_parser = new ServicesParser($response_data['services']);
            $services_parser->replaceServices();
        }
        
        return $response;
        
    }
    
}
