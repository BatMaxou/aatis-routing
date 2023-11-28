<?php

namespace Aatis\Core\Controller;

use Aatis\DependencyInjection\Entity\Container;

abstract class AbstractController
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }
}
