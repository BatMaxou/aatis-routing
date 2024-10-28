<?php

namespace Aatis\Routing\Service;

use Aatis\HttpFoundation\Component\Request;

class RequestStack
{
    /**
     * @var Request[]
     */
    private array $requests = [];

    public function push(Request $request): static
    {
        $this->requests[] = $request;

        return $this;
    }

    public function getCurrentRequest(): ?Request
    {
        return end($this->requests) ?: null;
    }
}
