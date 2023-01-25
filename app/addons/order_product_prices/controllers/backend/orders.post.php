<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'details') {
    Registry::set('navigation.tabs.prices', array(
        'title' => __('prices'),
        'js' => true
    ));
}
