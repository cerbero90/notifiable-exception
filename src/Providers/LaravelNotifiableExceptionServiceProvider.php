<?php

namespace Cerbero\LaravelNotifiableException\Providers;

use Cerbero\ExceptionHandler\Providers\ExceptionHandlerServiceProvider;
use Cerbero\LaravelNotifiableException\Notifiable;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;

/**
 * The Laravel notifiable exception service provider.
 *
 */
class LaravelNotifiableExceptionServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // register service provider to add custom handlers to Laravel exception handler
        $this->app->register(ExceptionHandlerServiceProvider::class);

        // register custom handler to notify notifiable exceptions
        $this->app->make(ExceptionHandler::class)->reporter(function (Notifiable $e) {
            $e->notify();
        });
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // publish the configuration, if requested
        $this->publishes([
            __DIR__ . '/../../config/notifiable_exception.php' => config_path('notifiable_exception.php'),
        ], 'notifiable_exception_config');

        // merge the published configuration with the package default one
        $this->mergeConfigFrom(__DIR__ . '/../../config/notifiable_exception.php', 'notifiable_exception');
    }
}
