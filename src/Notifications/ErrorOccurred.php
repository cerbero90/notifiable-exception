<?php

namespace Cerbero\LaravelNotifiableException\Notifications;

use Cerbero\LaravelNotifiableException\Notifiable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use RuntimeException;

class ErrorOccurred extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The notification messages keyed by the channel alias.
     *
     * @var array
     */
    protected $messagesByChannel;

    /**
     * The custom channel class names keyed by the channel alias.
     *
     * @var array
     */
    protected $customChannels;

    /**
     * Set the dependencies.
     *
     * @param \Cerbero\LaravelNotifiableException\Notifiable $exception
     */
    public function __construct(Notifiable $exception)
    {
        $this->messagesByChannel = $exception->getMessagesByChannel();
        $this->customChannels = $exception->getCustomChannels();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $channels = [];
        $aliases = array_keys($this->messagesByChannel);

        // if there is a custom channel associated with the channel alias
        // add custom channel to delivery channels, otherwise add the alias directly
        foreach ($aliases as $alias) {
            $channels[] = $this->customChannels[$alias] ?? $alias;
        }

        return $channels;
    }

    /**
     * Retrieve the notification message dynamically
     *
     * @param string $name
     * @param array $parameters
     * @return mixed
     * @throws \RuntimeException
     */
    public function __call($name, $parameters)
    {
        if (substr($name, 0, 2) !== 'to') {
            return;
        }

        $channel = strtolower(substr($name, 2));

        if (isset($this->messagesByChannel[$channel])) {
            return $this->messagesByChannel[$channel];
        }

        throw new RuntimeException("The channel [$channel] does not have any message to notify.");
    }
}
