<?php

namespace Aatis\Routing\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Route
{
    /**
     * @var class-string|null
     */
    private ?string $controller;

    private ?string $methodName;

    /**
     * @var array<string, string>
     */
    private array $methodParams = [];

    /**
     * @param string[] $httpMethodsAllowed
     */
    public function __construct(private string $path, private array $httpMethodsAllowed = [])
    {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string[]
     */
    public function gethttpMethodsAllowed(): array
    {
        return $this->httpMethodsAllowed;
    }

    /**
     * @return class-string|null
     */
    public function getController(): ?string
    {
        return $this->controller;
    }

    public function getMethodName(): ?string
    {
        return $this->methodName;
    }

    /**
     * @return array<string, string>
     */
    public function getMethodParams(): array
    {
        return $this->methodParams;
    }

    /**
     * @param class-string $controller
     */
    public function setController(string $controller): static
    {
        $this->controller = $controller;

        return $this;
    }

    public function setMethodName(string $methodName): static
    {
        $this->methodName = $methodName;

        return $this;
    }

    /**
     * @param array<string, string> $methodParams
     */
    public function setMethodParams(array $methodParams): static
    {
        $this->methodParams = $methodParams;

        return $this;
    }
}
