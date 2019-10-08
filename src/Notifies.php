<?php

namespace Cerbero\NotifiableException;

use Cerbero\NotifiableException\Notifications\ErrorOccurred;
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
    public function notify()
    {
        $defaultRoutes = config('notifiable_exception.default_routes');
        $routes = array_merge_recursive($defaultRoutes, $this->getCustomRoutes());

        /** @var \Cerbero\NotifiableException\Notifiable $this */
        $notification = new ErrorOccurred($this);

        foreach ($routes as $channel => $channelRoutes) {
            $channelRoutes = array_unique((array) $channelRoutes);

            foreach ($channelRoutes as $route) {
                (new AnonymousNotifiable)->route($channel, $route)->notify($notification);
            }
        }
    }

    /**
     * Retrieve the custom notification routes
     *
     * @return array
     */
    protected function getCustomRoutes(): array
    {
        return [];
    }

    /**
     * Retrieve the message for each channel keyed by the channel alias
     *
     * @return array
     */
    public function getMessages(): array
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
