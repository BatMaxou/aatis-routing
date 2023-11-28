<?php

namespace Aatis\Core;

use Dotenv\Dotenv;
use Aatis\Core\Service\Router;
use Aatis\DependencyInjection\Service\ContainerBuilder;

class Kernel
{
    public function handle(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../', ['.env', '.env.local'], false);
        $dotenv->load();

        $ctx = [
            'env' => $_ENV['APP_ENV'],
        ];

        $container = (new ContainerBuilder($ctx, $_ENV['DOCUMENT_ROOT'] . '/../src'))->build();

        /**
         * @var Router $router
         */
        $router = $container->get('Aatis\Core\Service\Router');

        $router->redirect();

        dd($container);
    }
}
