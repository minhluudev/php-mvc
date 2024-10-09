<?php

namespace Framework\Routing;

use Framework\Routing\Interfaces\RouteResolveInterface;

class RouteResolve implements RouteResolveInterface
{
    public const GET = 'GET';
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const DELETE = 'DELETE';
    protected static string $prefix = '';
    protected static array $routes = [];
    protected static array $middlewares = [];

    /**
     * @param string $method
     * @param string $path
     * @param mixed $action
     * @param array $middlewares
     * @return void
     */
    public static function mapPathAndMiddleware(string $method, string $path, mixed $action, array $middlewares = []): void
    {
        $path = trim($path, '/');
        $path = self::$prefix . "/$path";
        $middlewares = array_merge(self::$middlewares[self::$prefix] ?? [], $middlewares);
        self::$routes[$method][$path] = ['action' => $action, 'middlewares' => $middlewares];
    }

    public function resolve(): void
    {
        echo '<pre>';
        foreach (self::$routes as $method => $routes) {
            var_dump($routes);

        }

        echo '</pre>';
    }
}