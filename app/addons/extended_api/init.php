<?php

use Tygh\ExtendedAPI;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if (defined('API')) {
    Tygh::$app['api'] = new ExtendedAPI();
}
