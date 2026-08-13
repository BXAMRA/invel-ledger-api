<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Cache\RateLimiting\Limit;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void {}

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    RateLimiter::for("emails", function ($job) {
      return Limit::perMinute(10);
    });

    View::composer("components.email-layout", function ($view) {
      if (!$view->offsetExists("settings")) {
        $settings = Setting::all()
          ->pluck("value", "key")
          ->map(function ($val) {
            $decoded = json_decode($val, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $val;
          })
          ->toArray();
        $view->with("settings", $settings);
      }
    });
  }
}
