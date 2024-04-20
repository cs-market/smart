<?php

namespace Tygh\Addons\Equipment;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tygh\Addons\Equipment\Factory;
use Tygh\Addons\Equipment\Repository;
use Tygh\Tygh;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['addons.equipment.factory'] = static function (Container $app) {
            return new Factory($app);
        };

        $app['addons.equipment.repository'] = function (Container $app) {
            return new Repository($app['db']);
        };
    }
}
