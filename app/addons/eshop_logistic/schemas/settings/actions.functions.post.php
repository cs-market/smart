<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_eshop_logisic_cron_run_info()
{
    $admin_ind = Registry::get('config.admin_index');
    $cron_pass = Registry::get('addons.eshop_logistic.cron_pass');
    $root_directory = Registry::get('config.dir.root');

    $hint = '<b>' . __("eshop_logistic.cron_info") . ':</b><br /><code>php ' . $root_directory .'/' . $admin_ind . ' --dispatch=eshop_logistic.cron_clear_logs --cron_pass=' . $cron_pass . '</code>';
    
    return $hint;
}