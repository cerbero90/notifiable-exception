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
     * Notify the current exception.
     *
     * @return void
     */
    public function notify()
    {
        $defaultRoutes = config('notifiable_exception.default_routes');
        $routes = array_merge_recursive($defaultRoutes, $this->getAdditionalRoutes());

        /** @var \Cerbero\LaravelNotifiableException\Notifiable $this */
        $notification = new ErrorOccurred($this);

        foreach ($routes as $channel => $routes) {
            foreach ((array) $routes as $route) {
                (new AnonymousNotifiable)->route($channel, $route)->notify($notification);
            }
        }
    }

    /**
     * Retrieve the additional notification routes
     *
     * @return array
     */
    protected function getAdditionalRoutes()
    {
        return [];
    }

    /**
     * Retrieve the message for each channel keyed by the channel alias
     *
     * @return array
     */
    public function getMessagesByChannel()
    {
        return [];
    }

    /**
     * Retrieve the custom channel class names keyed by the channel alias
     *
     * @return array
     */
    public function getCustomChannels()
    {
        return [];
    }
}
