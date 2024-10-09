<?php

namespace Framework;

use Exception;

class Container
{
    private array $services = [];

    public function set($name, $closure): void
    {
        $this->services[$name] = $closure;
    }

    /**
     * @throws Exception
     */
    public function get($name): mixed
    {
        if (!isset($this->services[$name])) {
            throw new Exception("Service not found.");
        }

        return $this->services[$name]($this);
    }
}