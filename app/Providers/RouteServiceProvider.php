<?php

namespace App\Providers;

use Framework\Routing\Route;
use Framework\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Route::group(function () {
            include basePath("/routes/web.php");
        });

    }
}