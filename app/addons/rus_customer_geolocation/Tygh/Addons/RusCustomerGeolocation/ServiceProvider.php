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

namespace Tygh\Addons\RusCustomerGeolocation;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tygh\Registry;

class ServiceProvider implements ServiceProviderInterface
{
    /** @inheritdoc */
    public function register(Container $app)
    {
        $app['rus_customer_geolocation'] = function (Container $app) {
            $locator = new RusCustomerGeolocation(
                Registry::get('addons.rus_customer_geolocation'),
                Registry::get('settings.Checkout'),
                $app['db'],
                Registry::get('runtime.company_id'),
                CART_LANGUAGE
            );

            $locator->setLocation($app['rus_customer_geolocation.default_location']);

            return $locator;
        };

        $app['rus_customer_geolocation.default_location'] = function (Container $app) {
            $settings = Registry::get('settings.General');

            $location = new Location(
                $settings['default_country'],
                $settings['default_state'],
                $settings['default_city'],
                $settings['default_address'],
                $settings['default_zipcode'],
                CART_LANGUAGE
            );

            return $location;
        };
    }
}