<?php

namespace Framework;

class ServiceProvider
{
    protected array $services = [];

    public function __construct()
    {
        $this->register();
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $container = App::$app->container;

        foreach ($this->services as $name => $service) {
            $container->set($name, function () use ($service) {
                return new $service();
            });
        }
    }
}