<?php

namespace Cerbero\NotifiableException\Notifications;

use BadMethodCallException;
use Cerbero\NotifiableException\Notifiable;
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
    protected $messages;

    /**
     * The custom channel class names keyed by the channel alias.
     *
     * @var array
     */
    protected $customChannels;

    /**
     * Set the dependencies.
     *
     * @param \Cerbero\NotifiableException\Notifiable $exception
     */
    public function __construct(Notifiable $exception)
    {
        $this->messages = $exception->getMessages();
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
        $aliases = array_keys($this->messages);

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
     * @throws \BadMethodCallException
     * @throws \RuntimeException
     */
    public function __call($name, $parameters)
    {
        if (substr($name, 0, 2) !== 'to') {
            throw new BadMethodCallException(sprintf('Call to undefined method %s::%s()', static::class, $name));
        }

        $channel = strtolower(substr($name, 2));

        if (isset($this->messages[$channel])) {
            return $this->messages[$channel];
        }

        throw new RuntimeException("No message to send to the channel [$channel].");
    }
}
