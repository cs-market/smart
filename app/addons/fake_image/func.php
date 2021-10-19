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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_get_fake_image($product_id = 0) {
    $image_name = Registry::get('addons.fake_image.image');
    if (!empty($image_name)) {
        $img = array(
            'pair_id' => 'false',
            'image_id' => 0,
            'detailed_id' => 'false',
            'position' => 0,
            'detailed' => &$detailed,
        );
        $detailed = array(
            'object_id' => $product_id,
            'object_type' => 'product',
            'type' => 'M',
            'relative_path' => $image_name,
            'http_image_path' => Storage::instance('images')->getUrl($image_name, 'http'),
            'https_image_path' => Storage::instance('images')->getUrl($image_name, 'https'),
            'absolute_path' => Storage::instance('images')->getAbsolutePath($image_name),
            'image_path' => Storage::instance('images')->getUrl($image_name),
        );
        list($detailed['image_x'], $detailed['image_y'], ) = fn_get_image_size($detailed['absolute_path']);
        return $img;
    }
}

function fn_fake_image_get_product_data_post(&$product_data, $auth, $preview, $lang_code) {
    if (empty($product_data['main_pair']) || !is_file($product_data['main_pair']['detailed']['absolute_path'])) {
        $product_data['main_pair'] = fn_get_fake_image($product_data['product_id']);
    }   
}

function fn_fake_image_gather_additional_product_data_before_options(&$product_data, $auth, $params) {
    if (empty($product_data['main_pair']) || !is_file($product_data['main_pair']['detailed']['absolute_path'])) {
        $product_data['main_pair'] = fn_get_fake_image($product_data['product_id']);
    }
}
