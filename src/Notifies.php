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
        /** @var \Cerbero\NotifiableException\Notifiable $this */
        $notification = new ErrorOccurred($this);
        $routes = $this->getRoutesToNotify();

        foreach ($routes as $channel => $channelRoutes) {
            $channelRoutes = array_unique((array) $channelRoutes);

            foreach ($channelRoutes as $route) {
                (new AnonymousNotifiable)->route($channel, $route)->notify($notification);
            }
        }
    }

    /**
     * Retrieve all the routes to send notification to.
     *
     * @return array
     */
    protected function getRoutesToNotify(): array
    {
        if ($this->overridesRoutes()) {
            return $this->getCustomRoutes();
        }

        $defaultRoutes = config('notifiable_exception.default_routes');

        return array_merge_recursive($defaultRoutes, $this->getCustomRoutes());
    }

    /**
     * Determine whether the current exception routes should override the default ones.
     *
     * @return bool
     */
    protected function overridesRoutes(): bool
    {
        return false;
    }

    /**
     * Retrieve the custom notification routes keyed by the channel alias
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
