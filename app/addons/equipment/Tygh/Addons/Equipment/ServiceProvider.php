<?php

namespace Tygh\Addons\Equipment;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tygh\Tygh;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['addons.equipment.equipment_repository'] = function (Container $app) {
            return new EquipmentRepository($app['db']);
        };
        $app['addons.equipment.repair_requests_repository'] = function (Container $app) {
            return new RepairRequestsRepository($app['db']);
        };
    }
}
