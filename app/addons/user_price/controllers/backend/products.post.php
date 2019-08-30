<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'update') {
    Registry::set('navigation.tabs.user_price', array (
        'title' => __('user_price'),
        'js' => true
    ));
}
