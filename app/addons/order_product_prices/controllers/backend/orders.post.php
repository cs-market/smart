<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

if ($mode == 'details') {
    Registry::set('navigation.tabs.prices', array(
        'title' => __('prices'),
        'js' => true
    ));
}
