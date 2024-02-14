<?php

use Tygh\Registry;
use Tygh\Enum\SiteArea;
use Tygh\Enum\YesNo;
use Tygh\Enum\ObjectStatuses;

defined('BOOTSTRAP') or die('Access denied');

function fn_product_availability_update_product_pre(&$product_data, $product_id, $lang_code, $can_update) {
    if (!empty($product_data['avail_till'])) {
        $product_data['avail_till'] = fn_parse_date($product_data['avail_till']);
    }

    // check company tracking and set it by necessity
    if (empty($product_id) && !empty($product_data['company_id'])) {
        $fields = [];

        if (!isset($product_data['show_out_of_stock_product'])) {
            $fields[] = 'show_out_of_stock_product';
        }

        if (!isset($product_data['tracking'])) {
            $fields[] = 'tracking';
        }

        if (!isset($product_data['out_of_stock_actions'])) {
            $fields[] = 'out_of_stock_actions';
        }

        if (!empty($fields)) {
            $company_product_data = db_get_row('SELECT ' . implode(',', $fields) . ' FROM ?:companies WHERE company_id = ?i', $product_data['company_id']);
            $product_data = array_merge($product_data, $company_product_data);
        }
    }
}

function fn_product_availability_get_products($params, $fields, $sortings, &$condition, &$join, $sorting, $group_by, $lang_code, $having) {
    if (SiteArea::isStorefront(AREA)) {
        $condition .= db_quote(' AND IF(products.avail_till, products.avail_till >= ?i, 1)', TIME);

        // Cut off out of stock products
        if (Registry::get('addons.warehouses.status') == ObjectStatuses::ACTIVE) {
            $location_manager = Tygh::$app['location'];
            if ($destination_id = $location_manager->getDestinationId()) {
                $join .= db_quote(' RIGHT JOIN ?:warehouses_destination_products_amount AS wdpa ON wdpa.product_id = products.product_id AND wdpa.destination_id = ?i ', $destination_id);
                $condition .= db_quote(
                    ' AND (CASE products.show_out_of_stock_product' .
                    '   WHEN ?s THEN (
                        CASE products.is_stock_split_by_warehouses WHEN ?s'
                        . " THEN (wdpa.amount > 0 OR products.tracking = 'D')"
                        . ' ELSE 1 END
                    )' .
                    '   ELSE 1' .
                    ' END)',
                    YesNo::NO, YesNo::YES
                );
            }
        } else {
            $condition .= db_quote(
                ' AND (CASE products.show_out_of_stock_product' .
                "   WHEN ?s THEN (products.amount > 0 OR products.tracking = 'D')" .
                '   ELSE 1' .
                ' END)',
                'N'
            );
        }
    }
}

function fn_product_availability_get_product_data($product_id, $field_list, &$join, $auth, $lang_code, &$condition, $price_usergroup) {
    if (SiteArea::isStorefront(AREA)) {
        $condition .= db_quote(' AND IF(?:products.avail_till, ?:products.avail_till >= ?i, 1)', TIME);

        // Cut off out of stock products
        if (Registry::get('addons.warehouses.status') == ObjectStatuses::ACTIVE) {
            $location_manager = Tygh::$app['location'];
            if ($destination_id = $location_manager->getDestinationId()) {
                $join .= db_quote(' RIGHT JOIN ?:warehouses_destination_products_amount AS wdpa ON wdpa.product_id = ?:products.product_id AND wdpa.destination_id = ?i ', $destination_id);
                $condition .= db_quote(
                    ' AND (CASE ?:products.show_out_of_stock_product' .
                    '   WHEN ?s THEN (
                        CASE ?:products.is_stock_split_by_warehouses WHEN ?s'
                        . " THEN (wdpa.amount > 0 OR ?:products.tracking = 'D')"
                        . ' ELSE 1 END
                    )' .
                    '   ELSE 1' .
                    ' END)',
                    YesNo::NO, YesNo::YES
                );
            }
        } else {
            $condition .= db_quote(
                ' AND (CASE ?:products.show_out_of_stock_product' .
                "   WHEN ?s THEN (?:products.amount > 0 OR ?:products.tracking = 'D')" .
                '   ELSE 1' .
                ' END)',
                'N'
            );
        }
    }
}
