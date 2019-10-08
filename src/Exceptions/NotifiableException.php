<?php

namespace Cerbero\NotifiableException\Exceptions;

use Cerbero\NotifiableException\Notifiable;
use Cerbero\NotifiableException\Notifies;
use Exception;

/**
 * The base notifiable exception.
 *
 */
abstract class NotifiableException extends Exception implements Notifiable
{
    use Notifies;
}
