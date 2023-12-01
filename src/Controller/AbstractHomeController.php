<?php

namespace Aatis\Routing\Controller;

use Psr\Container\ContainerInterface;
use Aatis\Routing\Interface\HomeControllerInterface;

abstract class AbstractHomeController extends AbstractController implements HomeControllerInterface
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function home(): void
    {
        require_once $_ENV['DOCUMENT_ROOT'] . '/../views/pages/home.php';
    }
}
