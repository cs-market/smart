<?php

function fn_exim_orders_with_items_get_product_discount($data) {
    if (!empty($data)) {
        $data = @unserialize($data);
        if (!empty($data['discount'])) {
            return $data['discount'];
        }
    }

    return '';
}

function fn_exim_orders_with_items_get_product_promotions($data) {
    if (!empty($data)) {
        $data = @unserialize($data);
        if (!empty($data['promotions'])) {
            return implode(',', db_get_fields('SELECT external_id FROM ?:promotions WHERE promotion_id IN (?a) and external_id != ?s', array_keys($data['promotions']), ''));
        }
    }

    return '';
}
