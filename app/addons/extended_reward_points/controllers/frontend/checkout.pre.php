<?php

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //
    // Add product to cart
    //
    if ($mode == 'add') {
        if ($action == 'points_pay') {
            foreach ($_REQUEST['product_data'] as $key => &$data) {
                $data['extra']['points_pay'] = true;
            }
            unset($data);
        }
    }

    return [CONTROLLER_STATUS_OK];
}
