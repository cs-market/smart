<?php

namespace Tygh\Addons\EshopLogistic\Parsers;

use SplFileObject;
use Tygh\Tygh;

class CitiesParser
{
    private $filepath;
    private $csv_delimiter = ';';
    private $cities_once_count = 10000;
    private $logger;

    function __construct($filepath, $logger)
    {   
        $this->logger = $logger;
        $this->filepath = $filepath;    
    }

    public function parseCsv($page = 0)
    {    

        $f = false;
        if ($this->filepath !== false) {
            $f = new SplFileObject($this->filepath);
            $f->setFlags(SplFileObject::READ_CSV);
        }

        $f->seek(0);
        $cities_keys = $f->fgetcsv($this->csv_delimiter);

        $current_line = !empty($page) ? $this->cities_once_count * $page : 1;
        
        $f->seek($current_line);
        
        if ($f) {

            $file = file($this->filepath);
            $total_rows = count($file) - 1;

            $row = (int) $current_line;
            
            while (($data = $f->fgetcsv($this->csv_delimiter)) !== false) {
                                           
                $row ++;
                
                if ($row >= ((int) $current_line + $this->cities_once_count)) {
                    
                    if ($row >= $total_rows) {
                       
                        $this->logger->finishLog();
                        
                        break;
                    }

                    $next_page = $page + 1;
                    $redirect_url = 'eshop_logistic.get_cities_codes&page=' . $next_page;

                    $this->logger->finishLog();

                    if (defined('AJAX_REQUEST')) {
                        Tygh::$app['ajax']->assign('force_redirection', fn_url($redirect_url));
                    }else{
                        fn_redirect($redirect_url);
                    }
                }   
                
                if (!empty($data)) {

                    
                    $city = array_combine($cities_keys, $data);

                    $city_name  = !empty($city['name']) ? $city['name'] : '';
                    $city_state = !empty($city['region']) ? $city['region'] : '';
                    $city_fias  = !empty($city['fias']) ? $city['fias'] : '';
                    


                    if ($city_name == 'Москва' && empty($city_state)) {
                        $city_state = 'Москва';
                    }

                    if ($city_name == 'Санкт-Петербург' && empty($city_state)) {
                        $city_state = 'Санкт-Петербург';
                    }

                    if ($city_state == 'Санкт-Петербург город') {
                        $city_state = 'Санкт-Петербург';
                    }

                    

                    if (strpos(mb_strtolower($city_state), 'республика')) {
                        $city_state = str_replace(['республика', 'Республика'], ['', ''], $city_state);
                    }

                    if (mb_strtolower($city_name) == 'севастополь' && mb_strtolower(trim($city_state)) == 'крым') {
                        $city_state = 'Севастополь';
                    }

                    if (empty($city_name) || empty($city_state) || empty($city_fias)) {
                        
                        continue;
                    }


                    $this->findAndUpdateCity($city_name, $city_state, $city_fias);   
                }
            }
        }
        
    }

    private function findAndUpdateCity($city, $state, $fias)
    {
        

        $city_id = db_get_field("SELECT rcd.city_id FROM ?:state_descriptions as sd
            LEFT JOIN ?:states as s ON sd.state_id = s.state_id AND s.country_code = ?s
            LEFT JOIN ?:rus_cities as rc ON s.code = rc.state_code AND rc.country_code = ?s
            INNER JOIN ?:rus_city_descriptions as rcd ON rc.city_id = rcd.city_id AND rcd.city LIKE ?s AND rcd.lang_code = ?s
            WHERE sd.state LIKE ?s AND sd.lang_code = ?s",'RU', 'RU', $city, CART_LANGUAGE, "%".trim($state)."%", CART_LANGUAGE);

        if (!empty($city_id)) {
        
            db_query("UPDATE ?:rus_cities SET city_fias = ?s WHERE city_id = ?i", $fias, $city_id);       
        }
    }
}