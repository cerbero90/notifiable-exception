<?php

namespace Cerbero\LaravelNotifiableException;

use Cerbero\LaravelNotifiableException\Notifications\ErrorOccurred;
use Illuminate\Notifications\AnonymousNotifiable;

/**
 * Trait to let exceptions notify their errors.
 *
 */
trait Notifies
{
    /**
     * Define how the current exception should be reported by the exception handler
     *
     * @return void
     */
    public function report()
    {
        $this->notify();
    }

    /**
     * Notify the current exception.
     *
     * @return void
     */
    public function notify(): void
    {
        $defaultRoutes = config('notifiable_exception.default_routes');
        $routes = array_merge_recursive($defaultRoutes, $this->getAdditionalRoutes());

        /** @var \Cerbero\LaravelNotifiableException\Notifiable $this */
        $notification = new ErrorOccurred($this);

        foreach ($routes as $channel => $channelRoutes) {
            foreach ((array) $channelRoutes as $route) {
                (new AnonymousNotifiable)->route($channel, $route)->notify($notification);
            }
        }
    }

    /**
     * Retrieve the additional notification routes
     *
     * @return array
     */
    protected function getAdditionalRoutes(): array
    {
        return [];
    }

    /**
     * Retrieve the message for each channel keyed by the channel alias
     *
     * @return array
     */
    public function getMessagesByChannel(): array
    {
        return [];
    }

    /**
     * Retrieve the custom channel class names keyed by the channel alias
     *
     * @return array
     */
    public function getCustomChannels(): array
    {
        return [];
    }
}
