<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        Model::preventLazyLoading(! $this->app->isProduction());

        RateLimiter::for('login', function (Request $request): Limit {
            $emailAndIp = Str::lower($request->string('email')->toString()).'|'.$request->ip();

            return Limit::perMinute(5)->by(Str::transliterate($emailAndIp));
        });

        RateLimiter::for('signature', function (Request $request): Limit {
            $userAndIp = ($request->user()?->getAuthIdentifier() ?? 'guest').'|'.$request->ip();

            return Limit::perMinute(5)->by($userAndIp);
        });
    }
}
