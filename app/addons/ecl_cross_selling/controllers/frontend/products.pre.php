<?php
/*****************************************************************************
 *                                                                            *
 *                   All rights reserved! eCom Labs LLC                       *
 * http://www.ecom-labs.com/about-us/ecom-labs-modules-license-agreement.html *
 *                                                                            *
 *****************************************************************************/

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'view') {
    $product_id = !empty($_REQUEST['product_id']) ? $_REQUEST['product_id'] : 0;

    if (!empty($product_id)) {
        Registry::set('runtime.product_id', $product_id);
    }
}
