<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Setting;
use App\Models\License;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

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
    // Force HTTPS for Azure App Service
    if (config('app.env') === 'production') {
        URL::forceScheme('https');
    }

    // Prevent DB access during composer / artisan bootstrap
    if (App::runningInConsole()) {
        return;
    }

    try {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $setting = cache()->remember('app_setting', 3600, function () {
            return Setting::getSettings();
        });

        // Share globally
        view()->share('appSetting', $setting);

        // Apply timezone
        if (!empty($setting->timezone)) {
            config(['app.timezone' => $setting->timezone]);
            date_default_timezone_set($setting->timezone);
        }

        // Share pending license approvals with admin layout (Admin only)
        View::composer('layouts.admin', function ($view) {
            $pendingLicenses = collect();
            $pendingLicenseCount = 0;

            if (auth()->check() && Schema::hasTable('licenses')) {
                $user = auth()->user();
                $user->load('role'); // Ensure role is loaded for isAdmin() check

                if ($user->isAdmin()) {
                    $pendingLicenses = License::with(['vendor', 'creator'])
                        ->where('status', 'pending')
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                    $pendingLicenseCount = License::where('status', 'pending')->count();
                }
            }

            $view->with('pendingLicenses', $pendingLicenses);
            $view->with('pendingLicenseCount', $pendingLicenseCount);
        });
    } catch (\Throwable $e) {
        // Fail silently during early boot / install
        return;
    }
}
}
