<?php

namespace Tygh\Addons\EshopLogistic\Parsers;

use Tygh\Addons\EshopLogistic\Requests\Info;
use Tygh\Languages\Languages;

class ServicesParser
{
    private $services = [];
    private $current_services_keys = [];
    private $addon_name = "eshop_logistic";
    private $service_prefix = "eShopLogistic::";

    function __construct($services)
    {   
        $this->services = $services;
        $this->current_services_keys = $this->getCurrentShippingServices();    
    }

    private function getCurrentShippingServices()
    {

        $services = db_get_fields("SELECT `code` FROM ?:shipping_services WHERE module = ?s", $this->addon_name);

        return $services;
    }

    public function replaceServices()
    {
        $info_request = new Info();
        $delivery_services_info = $info_request->request();
        $services_for_insert = [];

        foreach ($this->services as $_service_key => $_service_data) {

            if (!empty($delivery_services_info->data)) {
                foreach ($delivery_services_info->data as $_ds_data) {
                    if (!empty($_ds_data->code && $_ds_data->code == $_service_key)) {
                        $current_eshop_services[] = $_ds_data->code . '_' . $_ds_data->type;
                        
                    }
                }
                
            }
        }

        $new_services = array_diff($current_eshop_services, $this->current_services_keys);
        $deleted_services = array_diff($this->current_services_keys, $current_eshop_services);
        
        if (!empty($deleted_services)) {
            
            $service_ids = db_get_fields('SELECT service_id FROM ?:shipping_services WHERE module = ?s AND code IN (?a)', $this->addon_name, $deleted_services);

            if (!empty($service_ids)) {
                
                db_query('DELETE FROM ?:shipping_services WHERE service_id IN (?a)', $service_ids);
                db_query('DELETE FROM ?:shipping_service_descriptions WHERE service_id IN (?a)', $service_ids);
            }

        }

        if (!empty($new_services)) {
            foreach($delivery_services_info->data as $service_data) {

                $s_code = $service_data->code . '_' . $service_data->type;

                if (in_array($s_code, $new_services)) {
                    
                    $services_for_insert[$s_code] = [
                        'status'        => 'A',
                        'module'        => $this->addon_name,
                        'code'          => $s_code,
                        'description'   => $this->service_prefix . $service_data->fullname
                    ];
                }
            }
        }
        
        if (!empty($services_for_insert)) {

            foreach ($services_for_insert as $service) {

                $service_id = db_query('INSERT INTO ?:shipping_services ?e', $service);
                $service['service_id'] = $service_id;
        
                foreach (Languages::getAll() as $service['lang_code'] => $lang_data) {
                    db_query('INSERT INTO ?:shipping_service_descriptions ?e', $service);
                }
            }
            
        }
        
    }
    
}