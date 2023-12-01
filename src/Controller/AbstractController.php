<?php

namespace Aatis\Routing\Controller;

use Aatis\DependencyInjection\Interface\ContainerInterface;

abstract class AbstractController
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
