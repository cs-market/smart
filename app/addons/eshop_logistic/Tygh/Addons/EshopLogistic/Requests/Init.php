<?php

namespace Tygh\Addons\EshopLogistic\Requests;

use Tygh\Enum\Addons\EshopLogistic\LoggerEnum;

class Init extends Request
{
    protected $apiPath = 'api/init';
    protected $logger_type;
    protected $cache_key = 'eshop_services_init';


    function __construct()
    {
        parent::__construct();
        
        $this->enableCache();
        $this->setLoggerType(LoggerEnum::SERVICES_INIT_REQUEST);   
    }
}
