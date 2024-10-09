<?php

namespace Framework;

use Framework\Routing\Route;

class App
{
    /**
     * The root path of the application.
     *
     * This property holds the root path of the application.
     *
     * @var string
     */
    public static string $basePath;
    /**
     * The application instance.
     *
     * This property holds the singleton instance of the application.
     *
     * @var App
     */
    public static $app;

    /**
     * The routing instance.
     *
     * This property holds the instance of the Route class.
     *
     * @var Route
     */
    public Route $route;

    /**
     * The service container instance.
     *
     * This property holds the instance of the Container class.
     *
     * @var Container
     */
    public Container $container;

    public function __construct($basePath)
    {
        self::$basePath = $basePath;
        self::$app = $this;
        $this->route = new Route();
        $this->container = new Container();
    }

    /**
     * Run the application.
     *
     * This method starts the application by executing the necessary
     * initialization and handling the incoming request.
     *
     * @return void
     */
    public function run(): void
    {
        $this->registerHelpers();
        $this->registerServiceProviders();
        $this->route->resolve();
    }

    /**
     * Register helpers.
     *
     * This method registers the helper functions.
     *
     * @return void
     */
    public function registerHelpers(): void
    {
        include self::$basePath . '/framework/Helper/Utils.php';
    }

    /**
     * Register service providers.
     *
     * This method registers the service providers defined in the
     * configuration file.
     *
     * @return void
     */
    public function registerServiceProviders(): void
    {
        $config = include_once self::$basePath . '/config/app.php';
        $providers = $config['providers'];

        foreach ($providers as $provider) {
            new $provider();
        }
    }
}