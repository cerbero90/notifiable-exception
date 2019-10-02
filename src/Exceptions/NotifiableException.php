<?php

namespace Cerbero\LaravelNotifiableException\Exceptions;

use Cerbero\LaravelNotifiableException\Notifiable;
use Cerbero\LaravelNotifiableException\Notifies;
use Exception;

/**
 * The base notifiable exception.
 *
 */
abstract class NotifiableException extends Exception implements Notifiable
{
    use Notifies;
}
