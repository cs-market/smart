<?php

namespace Tygh\Enum\Addons\EshopLogistic;

class LoggerEnum
{
    const SUCCESS   = 'S';
    const ERROR     = 'E';

    const SITE_REQUEST          = 'A';
    const CITIES_CODES_REQUEST  = 'B';
    const SERVICES_INIT_REQUEST = 'C';
    const DELIVERY_REQUEST      = 'D';
    const CITIES_SEARCH_REQUEST = 'E';
    const SERVICES_INFO_REQUEST = 'F';
    const GET_DATA_FOR_DELIVERY = 'G';

    public static function getLogTypeDescription($type) 
    {
        $desc = '';

        switch ($type) {
            case self::SITE_REQUEST:
                $desc = __('eshop_logistic.site_request');
                break;
            case self::CITIES_CODES_REQUEST:
                $desc = __('eshop_logistic.cities_codes_request');
                break;
            case self::SERVICES_INIT_REQUEST:
                $desc = __('eshop_logistic.services_init_request');
                break;
            case self::DELIVERY_REQUEST:
                $desc = __('eshop_logistic.delivery_request');
                break;
            case self::CITIES_SEARCH_REQUEST:
                $desc = __('eshop_logistic.cities_search_request');
                break;
            case self::SERVICES_INFO_REQUEST:
                $desc = __('eshop_logistic.services_info_request');
                break;
            case self::GET_DATA_FOR_DELIVERY:
                $desc = __('eshop_logistic.get_data_for_delivery');
                break;
        }

        return $desc;
    }
}