<?php

use Tygh\Registry;
use Tygh\Addons\Helpdesk\ServiceProvider;

defined('BOOTSTRAP') or die('Access denied');

Tygh::$app->register(new ServiceProvider());

fn_register_hooks('get_predefined_statuses');

Registry::set('config.storage.helpdesk_files', array(
    'prefix' => 'helpdesk_files',
    'secured' => true,
    'dir' => Registry::get('config.dir.var')
));
