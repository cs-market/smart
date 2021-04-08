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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return ;
}

if ($mode == 'update') {
    $params = $_REQUEST;
    $stickers = fn_get_stickers($params);
    $product = Registry::get('view')->getTemplateVars('product_data');
    $product_stickers = explode(',', $product['sticker_ids']);

    foreach ($stickers as &$sticker) {
        if ( in_array($sticker['sticker_id'], $product_stickers) ) {
            $sticker['selected'] = true;
        } else {
            $sticker['selected'] = false;
        }
    }
    Registry::get('view')->assign('stickers', $stickers);
}
