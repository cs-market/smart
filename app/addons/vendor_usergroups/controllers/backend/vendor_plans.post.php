<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return [CONTROLLER_STATUS_OK];
}

if ($mode == 'update' || $mode == 'add') {
    $id = 0;
    if ($mode == 'update') {
        $id = $_REQUEST['plan_id'];
    }
    $tabs = Registry::get('navigation.tabs');
    $tabs['usergroups_'.$id] = array(
        'title' => __('usergroups'),
        'js' => true,
    );

    Registry::set('navigation.tabs', $tabs);
}
