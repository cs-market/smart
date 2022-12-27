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
