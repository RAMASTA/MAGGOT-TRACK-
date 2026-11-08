<?php

namespace App\Providers;

use App\Services\NotificationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NotificationService::class);
    }

    public function boot(): void
    {
        date_default_timezone_set(config('maggottrack.timezone'));
        Carbon::setLocale('id');

        View::composer(['layouts.app', 'dashboard'], function ($view) {
            $view->with('notifications', Auth::check()
                ? app(NotificationService::class)->for(Auth::id())
                : collect());
        });
    }
}
