<?php

use Tygh\Registry;
use Tygh\Models\VendorPlan;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'update' || $mode == 'add') {
    $id = 0;
    if ($mode == 'update') {
        $plan = VendorPlan::model()->find($_REQUEST['plan_id']);
        $id = $plan->plan_id;
    }
    $tabs = Registry::get('navigation.tabs');
    $tabs['usergroups_'.$id] = array(
        'title' => __('usergroups'),
        'js' => true,
    );

    Registry::set('navigation.tabs', $tabs);
}