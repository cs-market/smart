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

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return [CONTROLLER_STATUS_OK];
}

if ($mode == 'export_found') {
    if (empty(Tygh::$app['session']['export_ranges'])) {
        Tygh::$app['session']['export_ranges'] = [];
    }

    if (empty(Tygh::$app['session']['export_ranges']['orders']['pattern_id'])) {
        Tygh::$app['session']['export_ranges']['orders'] = ['pattern_id' => 'orders'];
    }

    Tygh::$app['session']['export_ranges']['orders']['data_provider'] = [
        'count_function' => 'fn_exim_get_last_view_orders_count',
        'function'       => 'fn_exim_get_last_view_order_ids_condition',
    ];

    unset($_REQUEST['redirect_url'], Tygh::$app['session']['export_ranges']['orders']['data']);

    return [
        CONTROLLER_STATUS_OK,
        'exim.export?section=orders&pattern_id=' . Tygh::$app['session']['export_ranges']['orders']['pattern_id'],
    ];
}
