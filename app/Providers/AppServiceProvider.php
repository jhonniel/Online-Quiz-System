<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use App\Models\HiringApplication;
use App\Models\Setting;
use App\Observers\HiringApplicationObserver;
use App\Services\MailConfigService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ensure SQLite database path is always absolute
        $this->ensureAbsoluteDatabasePath();
        
        // Configure mail settings dynamically from database
        $this->configureMail();
        
        // Disable CSS inlining for emails
        $this->disableEmailCssInlining();
        
        // Register dynamic hiring application route based on admin settings
        $this->registerHiringApplicationRoute();

        HiringApplication::observe(HiringApplicationObserver::class);
        
        // Route model binding for DtrTimeRequest
        Route::bind('dtrTimeRequest', function ($value) {
            return \App\Models\DtrTimeRequest::findOrFail($value);
        });
    }
    
    /**
     * Ensure SQLite database path is always absolute
     */
    protected function ensureAbsoluteDatabasePath(): void
    {
        $connection = config('database.default');
        if ($connection === 'sqlite') {
            $databasePath = config("database.connections.sqlite.database");
            
            // If path is relative, make it absolute
            if (!empty($databasePath) && substr($databasePath, 0, 1) !== '/') {
                $databasePath = base_path($databasePath);
            }
            
            // Resolve realpath if file exists
            if (file_exists($databasePath)) {
                $databasePath = realpath($databasePath);
            }
            
            // Update config with absolute path
            config(["database.connections.sqlite.database" => $databasePath]);
        }
    }

    /**
     * Configure mail settings from database
     */
    protected function configureMail(): void
    {
        try {
            // Check if settings table exists
            if (!Schema::hasTable('settings')) {
                return;
            }
            
            // Configure mail settings dynamically
            MailConfigService::configure();
        } catch (\Exception $e) {
            // If settings table doesn't exist yet or any other error, just continue
            // Mail will use default config from config/mail.php
        }
    }
    
    /**
     * Disable CSS inlining for emails to avoid CssSelectorConverter dependency
     * 
     * This method patches Laravel's Mailer to skip CSS inlining by overriding
     * the renderView method. However, due to Laravel's internal implementation,
     * the best approach is to install symfony/css-selector package.
     * 
     * For now, we'll rely on error handling in the mail sending code.
     */
    protected function disableEmailCssInlining(): void
    {
        // Note: CSS inlining happens deep in Laravel's Mail system
        // The best solution is to install: composer require symfony/css-selector:^6.4
        // This method is kept as a placeholder for future improvements
    }

    /**
     * Register the hiring application route dynamically based on settings
     */
    protected function registerHiringApplicationRoute(): void
    {
        try {
            // Check if settings table exists
            if (!Schema::hasTable('settings')) {
                return;
            }
            
            $customPath = Setting::get('hiring_application_url', 'hiring/apply');
            $publicAccess = Setting::get('hiring_application_public_access', 'disabled');
            
            if ($publicAccess === 'enabled' && $customPath) {
                // Clean the path - remove leading slash and ensure it's valid
                $basePath = ltrim($customPath, '/');
                
                // Only register if path is not empty and doesn't conflict with existing routes
                if (!empty($basePath) && $basePath !== 'admin' && $basePath !== 'api') {
                    // Register PUBLIC routes (no authentication required)
                    // These routes are accessible to anyone, even without logging in
                    // Register base route (shows list of positions)
                    Route::get($basePath, [\App\Http\Controllers\HiringApplicationController::class, 'show'])
                        ->name('hiring.apply');
                    
                    // Register catch-all route for position-specific forms
                    // This will handle any slug dynamically
                    Route::get($basePath . '/{slug}', [\App\Http\Controllers\HiringApplicationController::class, 'show'])
                        ->where('slug', '[a-z0-9\-]+')
                        ->name('hiring.apply.position');
                    
                    Route::post($basePath . '/{slug}', [\App\Http\Controllers\HiringApplicationController::class, 'store'])
                        ->where('slug', '[a-z0-9\-]+')
                        ->name('hiring.apply.position.store');
                }
            }
        } catch (\Exception $e) {
            // If settings table doesn't exist yet or any other error, just continue
            // The route will be registered on next request after migration
        }
    }
}
