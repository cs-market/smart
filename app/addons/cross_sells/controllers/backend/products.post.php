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

defined('BOOTSTRAP') or die('Access denied');

if ($mode == 'update') {
    Registry::set('navigation.tabs.cross_sell', array (
        'title' => __('cross_sell'),
        'js' => true
    ));

    $cross_sells = [];
    if ($product_id = empty($_REQUEST['product_id']) ? 0 : intval($_REQUEST['product_id'])) {
        $cross_sells = db_get_hash_multi_array('SELECT related_id, related_type FROM ?:product_relations WHERE product_id = ?i', ['related_type', 'related_id', 'related_id'], $product_id);
    }
    Registry::get('view')->assign('cross_sells', $cross_sells);
}
