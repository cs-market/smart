<?php

function fn_import_order_items_fill_product_id(&$primary_object_id, &$object, &$pattern, &$options, &$processed_data, &$processing_groups, &$skip_record) {
    if ($id = db_get_field('SELECT product_id FROM ?:products LEFT JOIN ?:orders ON ?:orders.company_id = ?:products.company_id WHERE ?:orders.order_id = ?i and ?:products.product_code = ?s', $object['order_id'], $object['product_code'])) {
        $object['item_id'] = $object['product_id'] = $id;
    }
}
