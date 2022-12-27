<?php

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'maintenance/schemas/exim/orders_with_items.functions.php');

$schema['export_fields']['Product discount'] = array(
    'table' => 'order_details',
    'db_field' => 'extra',
    'linked' => true,
    'process_get' => array('fn_exim_orders_with_items_get_product_discount', '#this'),
);

return $schema;
