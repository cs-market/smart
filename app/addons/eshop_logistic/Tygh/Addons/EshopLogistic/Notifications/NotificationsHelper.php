<?php

namespace Tygh\Addons\EshopLogistic\Notifications;

class NotificationsHelper {

    public static function setWarning($message) 
    {
        self::showNotification('W', __('warning'), $message);
    }

    public static function setError($message) 
    {
        self::showNotification('E', __('error'), $message);
    }

    public static function setNotice($message) 
    {
        self::showNotification('N', __('notice'), $message);
    }

    private static function showNotification($type, $title, $mess) {
        
        if (defined("ESHOP_DEBUG")) {
            fn_set_notification($type, $title, $mess, '', 'demo_mode');
        }
    }


}