<?php

namespace Tygh\Addons\EshopLogistic\Requests;

use Tygh\Enum\Addons\EshopLogistic\LoggerEnum;

class Info extends Request
{
    protected $apiPath = 'api/info';
    protected $logger_type;
    protected $cache_key = 'eshop_services_info';


    function __construct()
    {
        parent::__construct();
        
        $this->enableCache();
        $this->setLoggerType(LoggerEnum::SERVICES_INFO_REQUEST);   
    }
}