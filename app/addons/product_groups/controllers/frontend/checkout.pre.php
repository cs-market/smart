<?php

defined('BOOTSTRAP') or die('Access denied');

$cart = &Tygh::$app['session']['cart'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_REQUEST['split_order']) && is_array($_REQUEST['split_order'])) {
        $cart['split_order'] = $_REQUEST['split_order'];
    }
}
