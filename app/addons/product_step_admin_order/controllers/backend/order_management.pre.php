<?php

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'add') {
        $product_id = empty($_REQUEST['product_id']) ? 0 : $_REQUEST['product_id'];
        $amount = db_get_field('SELECT CASE WHEN min_qty != 0 THEN min_qty ELSE qty_step END amount FROM ?:products WHERE product_id = ?i', $product_id);
        $_REQUEST['product_data'][$product_id]['amount'] = (!empty($amount)) ? $amount : 1;
    }

    return;
}
