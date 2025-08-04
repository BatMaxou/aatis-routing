<?php

namespace Aatis\Routing\Controller;

use Aatis\HttpFoundation\Component\Response;
use Aatis\Routing\Interface\ControllerInterface;
use Aatis\TemplateRenderer\Interface\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractController implements ControllerInterface
{
    public function __construct(
        protected readonly ContainerInterface $container,
        protected readonly TemplateRendererInterface $templateRenderer,
    ) {
    }

    /**
     * @param array<string, mixed> $vars
     */
    protected function render(string $template, array $vars = []): Response
    {
        return new Response($this->templateRenderer->render($template, $vars));
    }
}
