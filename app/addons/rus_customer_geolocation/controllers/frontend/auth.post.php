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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'login') {
        if ($user_id = Tygh::$app['session']['auth']['user_id']) {
            $user_info = fn_get_user_info($user_id);

            /** @var \Tygh\Addons\RusCustomerGeolocation\RusCustomerGeolocation $rus_customer_geolocation */
            $rus_customer_geolocation = Tygh::$app['rus_customer_geolocation'];
            $rus_customer_geolocation->setLocationFromArray(array(
                'country'     => $rus_customer_geolocation->getLocationField($user_info, 'country'),
                'state'       => $rus_customer_geolocation->getLocationField($user_info, 'state'),
                'city'        => $rus_customer_geolocation->getLocationField($user_info, 'city'),
                'zipcode'     => $rus_customer_geolocation->getLocationField($user_info, 'zipcode'),
                'address'     => $rus_customer_geolocation->getLocationField($user_info, 'address'),
                'is_detected' => true,
            ));

            $rus_customer_geolocation->storeLocation();
        }
    }
}

return array(CONTROLLER_STATUS_OK);