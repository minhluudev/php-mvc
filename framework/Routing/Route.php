<?php

namespace Framework\Routing;

use Framework\Routing\Interfaces\RouteInterface;

class Route extends RouteResolve implements RouteInterface
{
    /**
     * @param string $name
     * @param mixed $callback
     * @param array $middlewares
     * @return void
     */
    public static function prefix(string $name, mixed $callback, array $middlewares = []): void
    {
        $previousPrefix = self::$prefix;
        if ($name && $name !== '/') {
            $prefix = trim($name, '/');
            self::$prefix .= "/$prefix";
        }

        self::$middlewares[self::$prefix] = array_merge(self::$middlewares[self::$prefix] ?? [], $middlewares);
        call_user_func($callback);
        self::$prefix = $previousPrefix;
    }

    /**
     * @param mixed $callback
     * @param array $middlewares
     * @return void
     */
    public static function group(mixed $callback, array $middlewares = []): void
    {
        self::$middlewares[self::$prefix] = array_merge(self::$middlewares[self::$prefix] ?? [], $middlewares);
        call_user_func($callback);
    }

    /**
     * @param string $path
     * @param mixed $action
     * @param array $middlewares
     * @return void
     */
    public static function get(string $path, mixed $action, array $middlewares = []): void
    {
        self::mapPathAndMiddleware(self::GET, $path, $action, $middlewares);
    }

    /**
     * @param string $path
     * @param mixed $action
     * @param array $middlewares
     * @return void
     */
    public static function post(string $path, mixed $action, array $middlewares = []): void
    {
        self::mapPathAndMiddleware(self::POST, $path, $action, $middlewares);
    }
}