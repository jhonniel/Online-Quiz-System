<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;
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
        // Configure mail settings dynamically from database
        $this->configureMail();
        
        // Register dynamic hiring application route based on admin settings
        $this->registerHiringApplicationRoute();
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
