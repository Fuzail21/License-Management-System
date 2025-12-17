<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\App;

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
    } catch (\Throwable $e) {
        // Fail silently during early boot / install
        return;
    }
}
}
