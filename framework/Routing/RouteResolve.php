<?php

namespace Framework\Routing;

use Exception;
use Framework\App;
use Framework\Routing\Interfaces\RouteResolveInterface;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Class RouteResolve
 *
 * This class is responsible for resolving routes in the application.
 * It maps paths and middlewares to routes and resolves them based on the request method and URI.
 *
 * @package Framework\Routing
 */
class RouteResolve implements RouteResolveInterface {
    public const GET    = 'GET';
    public const POST   = 'POST';
    public const PUT    = 'PUT';
    public const DELETE = 'DELETE';
    protected static string $prefix      = '';
    protected static array  $routes      = [];
    protected static array  $middlewares = [];

    /**
     * Map the path and middleware.
     *
     * This method takes a request method, a path, an action, and an array of
     * middlewares, and maps them to the routes array.
     *
     * @param  string  $method  The request method.
     * @param  string  $path  The path to map.
     * @param  mixed   $action  The action to map.
     * @param  array   $middlewares  The middlewares to map.
     *
     * @return void
     */
    public static function mapPathAndMiddleware(string $method, string $path, mixed $action, array $middlewares = []): void {
        $path                         = trim($path, '/');
        $path                         = self::$prefix."/$path";
        $path                         = $path === '/' ? $path : trim($path, '/');
        $middlewares                  = array_merge(self::$middlewares[self::$prefix] ?? [], $middlewares);
        self::$routes[$method][$path] = ['action' => $action, 'middlewares' => $middlewares];
    }

    /**
     * Resolve the route.
     *
     * This method resolves the route by checking the request method and the
     * routes defined in the application. It then calls the appropriate method
     * to resolve the route.
     *
     * @return void
     * @throws Exception
     */
    public function resolve(): void {
        $method = App::$app->request->method();
        $routes = self::$routes[$method];
        if (!$routes) {
            throw new Exception('Route not found');
        }

        $this->resolveRoute($routes);
    }

    /**
     * Resolve the route.
     *
     * This method takes an array of routes and resolves the route. It checks
     * if the route is a normal path or a regex path and calls the appropriate
     * method to resolve the route.
     *
     * @param  array  $routes  The routes to resolve.
     *
     * @return void
     * @throws ReflectionException
     */
    private function resolveRoute(array $routes): void {
        $uri = App::$app->request->uri();

        if (isset($routes[$uri])) {
            $this->resolveWithNormalPath($routes[$uri]);
        } else {
            $this->resolveWithRegexPath($routes);
        }
    }

    /**
     * Resolve the route with a normal path.
     *
     * This method takes an array of routes and resolves the route with a normal
     * path. It calls the action of the route and passes the arguments to it.
     *
     * @param  array  $route  The route to resolve.
     * @param  array  $args  The arguments to pass to the action.
     *
     * @return void
     * @throws ReflectionException
     */
    private function resolveWithNormalPath(array $route, array $args = []): void {
        $action      = $route['action'];
        $middlewares = $route['middlewares'];

        self::handleMiddleware($middlewares);

        if (is_array($action)) {
            $request      = self::getRequestWithMethodCallback($action);
            $dependencies = DependencyInjection::resolveDependencies($action[0]);
            $action[0]    = new $action[0](...$dependencies);
        } else {
            $request = self::getRequestWithFunctionCallback($action);
        }

        echo call_user_func($action, $request, ...$args);
    }

    /**
     * Handle the middleware.
     *
     * This method iterates over an array of middleware names, retrieves each middleware
     * from the service container, and executes it. If any middleware cannot be retrieved,
     * an exception is caught, its message is displayed, and the script exits.
     *
     * @param  array  $middlewares  The middlewares to handle.
     *
     * @return void
     */
    private static function handleMiddleware(array $middlewares): void {
        $serviceContainer = App::$app->container;

        foreach ($middlewares as $middleware) {
            try {
                $serviceContainer->get("middleware:$middleware");
            } catch ( Exception $e ) {
                echo $e->getMessage();
                exit;
            }
        }
    }

    private static function getRequestWithMethodCallback($callback) {
        $params         = self::getParametersTypeMethodOrFunction($callback[1], $callback[0]);
        $paramFirstType = $params[0] ?? null;
        if ($paramFirstType && class_exists($paramFirstType)) {
            return new $paramFirstType();
        }

        return App::$app->request;
    }

    private static function getParametersTypeMethodOrFunction($method, $className = null): array {
        $paramTypes = [];

        if ($className) {
            $reflection = new ReflectionMethod($className, $method);
        } else {
            $reflection = new ReflectionFunction($method);
        }

        $params = $reflection->getParameters();
        foreach ($params as $param) {
            $type = $param->getType();

            if ($type !== null) {
                $paramTypes[] = $type->getName();
            }
        }

        return $paramTypes;
    }

    private static function getRequestWithFunctionCallback($callback) {
        $params         = self::getParametersTypeMethodOrFunction($callback);
        $paramFirstType = $params[0] ?? null;
        if ($paramFirstType && class_exists($paramFirstType)) {
            return new $paramFirstType();
        }

        return App::$app->request;
    }

    /**
     * Resolve the route with a regex path.
     *
     * This method takes an array of routes and resolves the route with a regex
     * path. It matches the URL against the route patterns and extracts the
     * arguments from the URL.
     *
     * @param  array  $routes  The routes to resolve.
     *
     * @return void
     * @throws ReflectionException
     */
    private function resolveWithRegexPath(array $routes): void {
        $uri = App::$app->request->uri();

        $dataMatched = $this->matchRouteData($routes, $uri);

        if ($dataMatched) {
            $action = $routes[$dataMatched['path']];
            $args   = $dataMatched['args'] ?? [];
            $this->resolveWithNormalPath($action, $args);
        } else {
            echo '404';
        }
    }

    /**
     * Match the URL against the route patterns.
     *
     * This method takes a URL and an array of route patterns, and matches the
     * URL against these patterns. If the URL matches a pattern, it extracts
     * the arguments from the URL.
     *
     * @param  array   $paths  The route patterns to match against.
     * @param  string  $url  The URL to match.
     *
     * @return array|null The matched route data if the URL matches a pattern, null otherwise.
     */
    private function matchRouteData(array $paths, string $url): array | null {
        $args   = false;
        $router = null;

        foreach ($paths as $path => $route) {
            $args = $this->argsMatched($url, $path);
            if ($args) {
                $router = $path;
                break;
            }
        }

        return $args && $router ? ['path' => $router, 'args' => $args] : null;
    }

    /**
     * Match the URL against the route pattern and extract arguments.
     *
     * This method takes a URL and a route pattern, converts the route pattern
     * to a regex pattern, and matches the URL against this pattern. If the URL
     * matches the pattern, it extracts the arguments from the URL.
     *
     * @param  string  $url  The URL to match.
     * @param  string  $patch  The route pattern to match against.
     *
     * @return array|null The extracted arguments if the URL matches the pattern, null otherwise.
     */
    private function argsMatched(string $url, string $patch): ?array {
        $args    = null;
        $pattern = $this->convertPathToPattern($patch);

        if (preg_match($pattern, $url, $matches)) {
            $args = $matches;
            array_shift($args);
        }

        return $args;
    }

    /**
     * Convert a route path to a regex pattern.
     *
     * This method takes a route path and converts it into a regex pattern
     * that can be used to match URLs. It replaces any route parameters
     * (denoted by a colon followed by a word, e.g., `:id`) with a regex
     * pattern that matches any word character.
     *
     * @param  string  $route  The route path to convert.
     *
     * @return string The regex pattern.
     */
    private function convertPathToPattern(string $route): string {
        $pattern = preg_replace('/(:\w+)/', '(\w+)', $route);

        return '/^'.str_replace('/', '\/', $pattern).'\/?(\/)?$/';
    }
}