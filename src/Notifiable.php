<?php

namespace Cerbero\LaravelNotifiableException;

use Throwable;

/**
 * Interface for notifiable exceptions.
 *
 */
interface Notifiable extends Throwable
{
    /**
     * Notify the current exception.
     *
     * @return void
     */
    public function notify();

    /**
     * Retrieve the message for each channel keyed by the channel alias
     *
     * @return array
     */
    public function getMessagesByChannel();

    /**
     * Retrieve the custom channel class names keyed by the channel alias
     *
     * @return array
     */
    public function getCustomChannels();
}
