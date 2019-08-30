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

use Tygh\Addons\RusCustomerGeolocation\RusCustomerGeolocation;
use Tygh\Tygh;

if ($stored_location = fn_get_session_data(RusCustomerGeolocation::SESSION_STORAGE_KEY)) {
    /** @var \Tygh\Addons\RusCustomerGeolocation\RusCustomerGeolocation $rus_customer_geolocation */
    $rus_customer_geolocation = Tygh::$app['rus_customer_geolocation'];

    $rus_customer_geolocation->setLocationFromArray($stored_location);
    $rus_customer_geolocation->storeLocation();
}

return array(CONTROLLER_STATUS_OK);