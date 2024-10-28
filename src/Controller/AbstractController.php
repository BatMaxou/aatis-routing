<?php

namespace Aatis\Routing\Controller;

use Aatis\HttpFoundation\Component\Response;
use Aatis\DependencyInjection\Interface\ContainerInterface;
use Aatis\TemplateRenderer\Interface\TemplateRendererInterface;

abstract class AbstractController
{
    public function __construct(
        protected readonly ContainerInterface $container,
        protected readonly TemplateRendererInterface $templateRenderer
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
