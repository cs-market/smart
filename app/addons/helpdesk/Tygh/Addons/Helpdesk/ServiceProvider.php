<?php

namespace Tygh\Addons\Helpdesk;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tygh\Registry;

/**
 * Class ServiceProvider is intended to register services and components of the "helpdesk" add-on to the application
 * container.
 *
 * @package Tygh\Addons\Barcode
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register(Container $app)
    {
        $reader_name = Registry::get('addons.helpdesk.mail_reader');
            
        $app["addons.helpdesk.$reader_name"] = function (Container $app) use ($reader_name) {
            $class = "\\Tygh\\Addons\\Helpdesk\\Readers\\" . fn_camelize($reader_name);
            return new $class();
        };

        $app['addons.helpdesk.mail_reader'] = function (Container $app) use ($reader_name) {
            return new MailReader($app["addons.helpdesk.$reader_name"]);
        };
    }
}
