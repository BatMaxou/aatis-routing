<?php

namespace Aatis\Routing\Controller;

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
     * @param array<string, mixed> $data
     */
    protected function render(string $template, array $data = []): void
    {
        $this->templateRenderer->render($template, $data);
    }
}
