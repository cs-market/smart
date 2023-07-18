<?php

defined('BOOTSTRAP') or die('Access denied');

if ($mode == 'print_invoice_for_payment') {
    if (!empty($_REQUEST['order_id'])) {
        echo(fn_print_order_invoices_for_payment($_REQUEST['order_id'], array(
        'pdf' => isset($_REQUEST['format']) && !empty($_REQUEST['format']) && $_REQUEST['format'] == 'pdf'
        )));
    }
    exit;
}
