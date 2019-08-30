<?php
/*****************************************************************************
 *                                                                            *
 *                   All rights reserved! eCom Labs LLC                       *
 * http://www.ecom-labs.com/about-us/ecom-labs-modules-license-agreement.html *
 *                                                                            *
 *****************************************************************************/

function fn_ecl_exim_get_related_products($product_id)
{
    if (empty($product_id)) {
        return '';
    }
    
    $related_products = fn_ecl_get_related_products($product_id);
    
    if (empty($related_products) || !is_array($related_products)) {
        return '';
    }

    $related_products = implode(',', $related_products);

    return $related_products;
}

function fn_ecl_exim_set_related_products($product_id, $related_products)
{
    if (empty($product_id)) {
        return true;
    }

    db_query('DELETE FROM ?:product_related_products WHERE product_id = ?i', $product_id);

    $related_products = explode(',', $related_products);

    foreach ($related_products as $related_product) {
        $check_product_id = db_get_field('SELECT product_id FROM ?:products WHERE product_id = ?i', $related_product);

        if (empty($check_product_id)) {
            continue;
        }

        $check_related_product_id = db_get_field('SELECT product_id FROM ?:product_related_products WHERE product_id = ?i AND related_id = ?i', $product_id, $related_product);

        if (!empty($check_related_product_id)) {
            continue;
        }

        $data = array(
            'product_id' => $product_id,
            'related_id' => $related_product
        );

        db_query('INSERT INTO ?:product_related_products ?e', $data);
    }
}
