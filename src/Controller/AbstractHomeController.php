<?php

namespace Aatis\Routing\Controller;

use Aatis\Routing\Interface\HomeControllerInterface;

abstract class AbstractHomeController extends AbstractController implements HomeControllerInterface
{
    public function home(): void
    {
        require_once '../vendor/aatis/routing/templates/welcome.tpl.php';
    }
}
