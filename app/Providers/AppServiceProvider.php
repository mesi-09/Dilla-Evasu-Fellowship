<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        if (config('app.env') !== 'production') {
            URL::forceRootUrl(config('app.url'));

            if (str_starts_with(config('app.url'), 'https://')) {
                URL::forceScheme('https');
            }
        }

        $this->configureRateLimiting();
    }

    protected function configureRateLimiting(): void
    {
        // Signed-in people are counted by account, so a whole campus sharing
        // one network address does not share one limit. Visitors have no
        // account, so they are counted by address.
        $who = fn (Request $request) => $request->user()?->id ?: $request->ip();

        RateLimiter::for('public-forms', fn (Request $request) => Limit::perHour(10)->by($request->ip()));
        RateLimiter::for('submit-request', fn (Request $request) => Limit::perHour(10)->by($who($request)));
        RateLimiter::for('chat', fn (Request $request) => Limit::perMinute(20)->by($who($request)));
        RateLimiter::for('community-write', fn (Request $request) => Limit::perMinute(10)->by($who($request)));
        RateLimiter::for('reactions', fn (Request $request) => Limit::perMinute(60)->by($who($request)));
        RateLimiter::for('registrations', fn (Request $request) => Limit::perHour(20)->by($request->ip()));
    }
}