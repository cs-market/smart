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
use Tygh\Storage;

defined('BOOTSTRAP') or die('Access denied');

function fn_get_fake_image($object_id = 0, $img = [], $object_type = 'product', $image_type = 'detailed') {
    $image_name = Registry::get('addons.fake_image.image');
    if (!empty($image_name)) {
        if (empty($img)) {
            $img = array(
                'pair_id' => -1,
                'image_id' => 0,
                'detailed_id' => -1,
                'position' => 0,
            );
        }
        $img[$image_type] = &$detailed;
        $detailed = array(
            'object_id' => $object_id,
            'object_type' => $object_type,
            'type' => 'M',
            'relative_path' => $image_name,
            'http_image_path' => Storage::instance('images')->getUrl($image_name, 'http'),
            'https_image_path' => Storage::instance('images')->getUrl($image_name, 'https'),
            'absolute_path' => Storage::instance('images')->getAbsolutePath($image_name),
            'image_path' => Storage::instance('images')->getUrl($image_name),
            'alt' => '',
        );
        list($detailed['image_x'], $detailed['image_y'], ) = fn_get_image_size($detailed['absolute_path']);
        return $img;
    }
}

function fn_fake_image_get_product_data_post(&$product_data, $auth, $preview, $lang_code) {
    if (empty($product_data['main_pair']) || !is_file($product_data['main_pair']['detailed']['absolute_path'])) {
        $product_data['main_pair'] = fn_get_fake_image($product_data['product_id'], $product_data['main_pair']);
    }
}

function fn_fake_image_get_promotions_post($params, $items_per_page, $lang_code, &$promotions) {
    foreach ($promotions as &$promotion) {
        if (empty($promotion['image']) || !is_file($promotion['image']['icon']['absolute_path'])) {
            $promotion['image'] = fn_get_fake_image($promotion['promotion_id'], "", 'promotion', 'icon');
        }
    }
}

function fn_fake_image_gather_additional_product_data_before_options(&$product_data, $auth, $params) {
    if (empty($product_data['main_pair']) || !is_file($product_data['main_pair']['detailed']['absolute_path'])) {
        $product_data['main_pair'] = @fn_get_fake_image($product_data['product_id'], $product_data['main_pair']);
    }
}

function fn_fake_image_delete_image_pair($pair_id, $object_type, $images) {
    if (!empty($images)) {
        if ($images['image_id']) fn_delete_image_unconditionally($images['image_id'], $pair_id, $object_type);
        if ($images['detailed_id']) fn_delete_image_unconditionally($images['detailed_id'], $pair_id, 'detailed');
    }
}

function fn_delete_image_unconditionally($image_id, $pair_id, $object_type = 'product')
{
    if (AREA == 'A' && fn_allowed_for('MULTIVENDOR') && Registry::get('runtime.company_id') && $object_type == 'category') {
        return false;
    }

    $image_file = fn_get_image_file_by_id($image_id);

    fn_set_hook('delete_image_pre', $image_id, $pair_id, $object_type);

    $type = ($object_type == 'detailed' ? 'detailed_id' : 'image_id');
    db_query('UPDATE ?:images_links SET ?f = ?s WHERE pair_id = ?i', $type, '0', $pair_id);
    $ids = db_get_row('SELECT image_id, detailed_id FROM ?:images_links WHERE pair_id = ?i', $pair_id);

    if (empty($ids['image_id']) && empty($ids['detailed_id'])) {
        db_query('DELETE FROM ?:images_links WHERE pair_id = ?i', $pair_id);
    }


    fn_delete_image_file($image_id, $object_type, $image_file);

    fn_set_hook('delete_image', $image_id, $pair_id, $object_type, $image_file);

    return true;
}
