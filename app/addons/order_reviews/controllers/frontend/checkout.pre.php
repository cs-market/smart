<?php

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return array(CONTROLLER_STATUS_OK);
}

if ($mode == 'complete' && !empty($_REQUEST['order_id'])) {
    return array(CONTROLLER_STATUS_REDIRECT, 'orders.details?order_id='.$_REQUEST['order_id']);
}
