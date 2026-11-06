<?php

namespace Modules\Auth\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Support\Facades\RateLimiter;

class AuthServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Auth';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'auth';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Define module schedules.
     *
     * @param  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }

    public function boot(): void
    {
        parent::boot();

        RateLimiter::for('check-user', function (Request $request) {
            return app()->isLocal() ? Limit::none() : Limit::perMinutes(1,5)->by($request->userAgent() . ":" . $request->ip());
        });

        RateLimiter::for('verification-code', function (Request $request) {
            return app()->isLocal() ? Limit::none() : Limit::perMinutes(10,20)->by($request->userAgent() . ":" . $request->ip());
        });

        RateLimiter::for('auth_user', function (Request $request) {
            return app()->isLocal() ? Limit::none() : Limit::perMinutes(1,2)->by($request->userAgent() . ":" . $request->ip());
        });
    }
}
