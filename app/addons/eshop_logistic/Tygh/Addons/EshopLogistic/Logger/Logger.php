<?php

namespace Tygh\Addons\EshopLogistic\Logger;

use Tygh\Enum\Addons\EshopLogistic\LoggerEnum;
use Tygh\Registry;

class Logger
{
    protected $log_type = '';
    protected $start_time = 0;
    protected $end_time = 0;
    protected $log_id = 0;
    protected $log_table = "?:eshop_logistic_logs";
    protected $log_status = LoggerEnum::SUCCESS;
    protected $log_message = '';
    protected $log_data = '';
    protected $caching = 'Y';

    function __construct()
    {   
        $this->start_time = time();

        if (Registry::get('addons.eshop_logistic.eshop_use_logging') == 'Y') {
            $this->createLog();
        }
        
    }

    public function setError()
    {
        $this->log_status = LoggerEnum::ERROR;
    }

    public function setType($type)
    {
        $this->log_type = $type;
    }

    public function isCaching()
    {
        $this->caching = 'Y';
    }

    public function setMessAndData($mess = '', $data = '')
    {
        $this->log_message  .= $mess;
        $this->log_data     .= $data;
    }

    public function getLogId()
    {
        return $this->log_id;
    }

    public function finishLog()
    {   
        if (Registry::get('addons.eshop_logistic.eshop_use_logging') == 'Y') {
            $this->end_time = time();

            $log_data = $this->buildLogData();
            
            db_query("UPDATE $this->log_table SET ?u WHERE log_id = ?i", $log_data, $this->log_id);
        }
    }

    protected function getLogTime()
    {
        if ($this->start_time > $this->end_time) {
            return 0;
        }else {
            return $this->end_time - $this->start_time;
        }
    }

    protected function createLog()
    {
        
        $start_data = $this->buildLogData();
        $this->log_id = db_query("INSERT INTO $this->log_table ?e", $start_data);
    }

    protected function buildLogData()
    {
        $data = array(
            'start_time' => $this->start_time,
            'time'       => $this->getLogTime(),
            'status'     => $this->log_status,
            'message'    => $this->log_message,
            'type'       => $this->log_type,
            'data'       => $this->log_data,
            'caching'    => $this->caching
        );

        return $data;
    }

    

}