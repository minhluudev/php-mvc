<?php

namespace Framework\Routing\Interfaces;

interface RouteResolveInterface
{
    public static function mapPathAndMiddleware(string $method, string $path, mixed $action, array $middlewares = []): void;

    public function resolve(): void;
}