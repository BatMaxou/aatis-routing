<?php

namespace Aatis\Routing\Controller;

use Aatis\HttpFoundation\Component\Response;

final class AatisController extends AbstractController
{
    public function home(): Response
    {
        return $this->render('welcome.tpl.php', ['overrideLocation' => '../vendor/aatis/routing/templates']);
    }
}
