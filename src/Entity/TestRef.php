<?php

namespace Aatis\Core\Entity;

class TestRef
{
    public function __construct(
        private string $name,
        private Test $test,
        private int $nb
    ) {
        $this->name = $name;
        $this->test = $test;
        $this->nb = $nb;
    }
}
