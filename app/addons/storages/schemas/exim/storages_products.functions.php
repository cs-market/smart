<?php
/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*      Copyright (c) 2013 CS-Market Ltd. All rights reserved.             *
*                                                                         *
*  This is commercial software, only users who have purchased a valid     *
*  license and accept to the terms of the License Agreement can install   *
*  and use this program.                                                  *
*                                                                         *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*  PLEASE READ THE FULL TEXT OF THE SOFTWARE LICENSE AGREEMENT IN THE     *
*  "license agreement.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.  *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * **/

use Tygh\Registry;

function fn_storages_products_exim_get_primary_object_id(&$alt_keys, &$skip_get_primary_object_id) {
    if (!(isset($alt_keys['code']) && isset($alt_keys['product_code']))) {
        $skip_get_primary_object_id = true;
    } else {
        $alt_keys = [
            'storage_id' => $alt_keys['code'],
            'product_id' => $alt_keys['product_code']
        ];
    }

    return true;
}

function fn_storage_exim_check_primary_object_id(&$data, &$skip_record, $processed_data) {
    if (empty($data['code']) || empty($data['product_code'])) {
        $processed_data['S']++;
        $skip_record = true;
    } else {
        $data['storage_id'] = $data['code'];
        $data['product_id'] = $data['product_code'];
        unset($data['code'], $data['product_code']);
    }

    return true;
}

function fn_storages_exim_get_storage_id($data) {
    static $storages = [];

    if (!isset($storages[$data])) {
        $condition = '';
        if (fn_allowed_for('MULTIVENDOR')) {
            $condition = db_quote(' AND company_id = ?i', Registry::get('runtime.company_id'));
        }

        $storages[$data] = db_get_field("SELECT storage_id FROM ?:storages WHERE code = ?s $condition", $data);
    }

    return $storages[$data];
}

function fn_storages_exim_get_product_id($data) {
    static $products = [];

    if (!isset($products[$data])) {
        $condition = '';
        if (fn_allowed_for('MULTIVENDOR')) {
            $condition = db_quote(' AND company_id = ?i', Registry::get('runtime.company_id'));
        }

        $products[$data] = db_get_field("SELECT product_id FROM ?:products WHERE product_code = ?s $condition", $data);
    }

    return $products[$data];
}
