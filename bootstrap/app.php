<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        then: function () {
            Route::middleware(['web', 'guest'])
                ->group(base_path('routes/auth.php'));

            Route::middleware(['web', 'auth'])
                ->group(base_path('routes/dashboard.php'));

            Route::middleware(['web', 'auth'])
                ->group(base_path('routes/servers.php'));

            Route::middleware(['web', 'auth'])
                ->group(base_path('routes/shop.php'));

            Route::middleware(['web', 'auth'])
                ->group(base_path('routes/cart.php'));

            Route::prefix('admin')->name('admin.')->middleware([])->group(function() {
                Route::group([], base_path('routes/admin/products.php'));
                Route::group([], base_path('routes/admin/servers.php'));
                Route::group([], base_path('routes/admin/users.php'));
                Route::group([], base_path('routes/admin/store.php'));
            });
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
