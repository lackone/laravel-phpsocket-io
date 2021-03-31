<?php

namespace Lackone\LaravelPhpsocketIo\Providers;

use Illuminate\Support\ServiceProvider;
use Lackone\LaravelPhpsocketIo\Commands\PhpSocketIOCommand;

class PhpSocketIOServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (app()->environment() == 'local' || app()->environment() == 'testing') {
            error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
        } else {
            error_reporting(0);
        }

        $this->mergeConfigFrom(__DIR__ . '/../../config/ps.php', 'ps');

        if ($this->app->runningInConsole()) {
            $this->commands([
                PhpSocketIOCommand::class,
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/ps.php' => config_path('ps.php'),
        ], 'config');
    }
}