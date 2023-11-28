<?php

namespace Aatis\Core\Controller;

use Aatis\Core\Interface\HomeControllerInterface;
use Aatis\DependencyInjection\Entity\Container;

abstract class AbstractHomeController extends AbstractController implements HomeControllerInterface
{
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function home(): void
    {
        require_once $_ENV['DOCUMENT_ROOT'] . '/../views/pages/home.php';
    }
}
