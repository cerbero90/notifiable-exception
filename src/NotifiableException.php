<?php

namespace Cerbero\LaravelNotifiableException;

use Exception;

/**
 * The base notifiable exception.
 *
 */
abstract class NotifiableException extends Exception implements Notifiable
{
    use Notifies;
}
