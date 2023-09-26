<?php

namespace Tygh\Addons\Telegram;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tygh\Registry;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['addons.telegram.messenger'] = function(Container $app) {
            return new Messenger();
        };

        $app['addons.telegram.render_manager'] = function(Container $app) {
            return new RenderManager();
        };
    }
}
