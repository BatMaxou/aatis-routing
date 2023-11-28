<?php

namespace Aatis\Core\Controller;

use Aatis\Core\Entity\Route;
use Aatis\DependencyInjection\Entity\Container;

class AatisController extends AbstractHomeController
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    #[Route('/')]
    public function home(): void
    {
        parent::home();
    }

    #[Route('/hello')]
    public function hello(): void
    {
        require_once $_ENV['DOCUMENT_ROOT'] . '/../views/pages/hello.php';
    }

    #[Route('/hello/{name}')]
    public function helloName(string $name): void
    {
        require_once $_ENV['DOCUMENT_ROOT'] . '/../views/pages/helloName.php';
    }
}
