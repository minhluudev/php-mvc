<?php

namespace Framework\Routing\Interfaces;

interface RouteInterface
{
    public static function prefix(string $name, mixed $callback, array $middlewares = []): void;

    public static function group(mixed $callback, array $middlewares = []): void;

    public static function get(string $path, mixed $action, array $middlewares = []): void;

    public static function post(string $path, mixed $action, array $middlewares = []): void;
}