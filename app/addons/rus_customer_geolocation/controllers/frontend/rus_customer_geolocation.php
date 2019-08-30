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

/** @var \Tygh\Addons\RusCustomerGeolocation\RusCustomerGeolocation $rus_customer_geolocation */
$rus_customer_geolocation = Tygh::$app['rus_customer_geolocation'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'set_location') {

        $params = array_merge(array(
            'country'     => null,
            'state'       => null,
            'city'        => null,
            'is_detected' => true,
        ), $_REQUEST);

        if ($locations = $rus_customer_geolocation->getPredefinedLocations()) {
            if (!isset($locations[$params['country']]['states'][$params['state']]['cities']) ||
                !in_array($params['city'], $locations[$params['country']]['states'][$params['state']]['cities'])
            ) {
                $params['country'] = key($locations);
                $params['state'] = key($locations[$params['country']]['states']);
                $params['city'] = reset($locations[$params['country']]['states'][$params['state']]['cities']);
            }
        }

        $rus_customer_geolocation->setLocationFromArray($params);

        $rus_customer_geolocation->storeLocation(true);

        $cart = & Tygh::$app['session']['cart'];
        $cart['recalculate'] = true;
        $cart['calculate_shipping'] = true;

        if (defined('AJAX_REQUEST')) {
            Tygh::$app['ajax']->assign('city', $rus_customer_geolocation->getLocation()->getCity());
            exit;
        }
    }
}

if ($mode == 'get_locations') {
    Tygh::$app['view']->assign(array(
        'locations' => $rus_customer_geolocation->getPredefinedLocations(),
    ));
}

return array(CONTROLLER_STATUS_OK);
