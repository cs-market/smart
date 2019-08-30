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
    if ($mode == 'update') {
        $params = array_merge(array(
            'locations' => array(),
        ), $_REQUEST);

        /** @var \Tygh\Addons\RusCustomerGeolocation\RusCustomerGeolocation $rus_customer_geolocation */
        $rus_customer_geolocation = Tygh::$app['rus_customer_geolocation'];

        $rus_customer_geolocation->setPredefinedLocations($params['locations']);
    }

    return array(CONTROLLER_STATUS_OK, 'rus_customer_geolocation.manage');
}

if ($mode == 'manage') {

    /** @var \Tygh\Addons\RusCustomerGeolocation\RusCustomerGeolocation $rus_customer_geolocation */
    $rus_customer_geolocation = Tygh::$app['rus_customer_geolocation'];

    $countries = $rus_customer_geolocation->getCountries();
    $states = $rus_customer_geolocation->getStates();
    $locations = $rus_customer_geolocation->getPredefinedLocations();

    Tygh::$app['view']->assign(array(
        'countries' => $countries,
        'states'    => $states,
        'locations' => $locations,
    ));
}