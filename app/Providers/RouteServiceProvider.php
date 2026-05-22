<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // Load routes FIRST before anything else
        $this->mapWebRoutes();

        if (file_exists(base_path('routes/api.php'))) {
            $this->mapApiRoutes();
        }

        $this->configureRateLimiting();

        // Routes are registered in boot() before all providers finish; rebuild name/action lookups
        // so route() and Route::has() work for every named route (e.g. quizzes.results).
        $this->app->booted(function () {
            $routes = $this->app['router']->getRoutes();

            if ($routes instanceof RouteCollection) {
                $routes->refreshNameLookups();
                $routes->refreshActionLookups();
            }
        });
    }

    /**
     * Define the "web" routes for the application.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     */
    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('say-it-ai-image', function (Request $request) {
            return Limit::perMinute(8)->by($request->ip());
        });
    }
}
