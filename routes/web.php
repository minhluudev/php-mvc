<?php

use App\HTTP\Controllers\Admin\OverviewController;
use App\HTTP\Controllers\Web\AuthController;
use App\HTTP\Controllers\Web\HomeController;
use Framework\Routing\Route;


// Web routes
Route::get('/', [HomeController::class, 'index']);
Route::get('login', [AuthController::class, 'login']);
//Route::post('login', [AuthController::class, 'handleLogin']);
Route::get('register', [AuthController::class, 'register']);
//Route::post('register', [AuthController::class, 'register']);

// Admin routes
Route::prefix('admin', function () {
    Route::get('/', [OverviewController::class, 'index']);
}, ['auth']);