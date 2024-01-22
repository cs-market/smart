<?php

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'add' && $action == 'wishlist') {
        $_REQUEST['product_data'] = Tygh::$app['session']['wishlist']['products'];
    }
    return array(CONTROLLER_STATUS_OK);
}
