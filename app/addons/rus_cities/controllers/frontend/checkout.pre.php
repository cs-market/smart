<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

defined('BOOTSTRAP') or die('Access denied');

use Tygh\Tygh;

/** @var string $mode */

if ($mode == 'cart' || $mode == 'shipping_estimation') {

    $customer_location = !empty($_REQUEST['customer_location'])
        ? array_map('trim', $_REQUEST['customer_location'])
        : array();
    Tygh::$app['session']['stored_location'] = $customer_location;

    $location = fn_rus_cities_get_location_from_session($mode == 'shipping_estimation');

    if ($location) {
        list($cities,) = fn_get_cities(array(
            'country_code' => $location['s_country'],
            'state_code'   => $location['s_state'],
            'status'       => 'A',
        ), 0, DESCR_SL);

        Tygh::$app['view']->assign(array(
            'cities'       => $cities,
            'customer_loc' => $location,
        ));
    }
}

return array(CONTROLLER_STATUS_OK);
