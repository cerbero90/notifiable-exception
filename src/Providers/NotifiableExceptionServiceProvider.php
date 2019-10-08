<?php

namespace Cerbero\NotifiableException\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * The notifiable exception service provider.
 *
 */
class NotifiableExceptionServiceProvider extends ServiceProvider
{
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
