<?php

use Tygh\Addons\Equipment\ServiceProvider;

defined('BOOTSTRAP') or die('Access denied');

define('STATUS_MALFUNCTION', 'M');

fn_register_hooks('get_status_params_definition');

Tygh::$app->register(new ServiceProvider());
