<?php

namespace Kingshark\RouteChecker;

use Illuminate\Support\ServiceProvider;
use Kingshark\RouteChecker\Console\Commands\RouteCheck;

class RouteCheckerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                RouteCheck::class,
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
