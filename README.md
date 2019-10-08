# Notifiable Exception

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Laravel package to send [notifications](https://laravel.com/docs/notifications) when exceptions are thrown.

## Install

Via Composer

``` bash
$ composer require cerbero/notifiable-exception
```

We might need to install other packages depending on the notification channels we want to use (e.g. Slack, Telegram). Please refer to [Laravel Notification Channels](http://laravel-notification-channels.com) for more information.

For better performance notifications are queued, please check the [documentation](https://laravel.com/docs/queues) to find out what are the requirements for your queue driver.

## Usage

In order to be notifiable, exceptions need to implement the `Notifiable` interface and use the `Notifies` trait:
``` php
use Cerbero\NotifiableException\Notifiable;
use Cerbero\NotifiableException\Notifies;
use Exception;

class UrgentException extends Exception implements Notifiable
{
    use Notifies;
}
```

Otherwise, if we don't need to extend a particular exception class, we may just extend the `NotifiableException` for convenience:
``` php
use Cerbero\NotifiableException\Exceptions\NotifiableException;

class UrgentException extends NotifiableException
```

When notifiable exceptions are not handled manually in a `try-catch`, they are notified automatically. However when we actually need to handle them we can send their notifications by calling the `notify()` method in the `try-catch`:
``` php
try {
    $this->methodThrowingNotifiableException();
} catch (NotifiableException $e) {
    $e->notify();
    // exception handling logic
}
```

Sometimes we might want some channel routes to always be notified when an exception is thrown. If so, we can set default routes for every channel we want to notify in `config/notifiable_exception.php`:
``` bash
$ php artisan vendor:publish --tag=notifiable_exception_config
```

As an example, the following configuration defines a Slack and a mail route that will always be notified when any notifiable exception is thrown:
``` php
'default_routes' => [
    'mail' => [
        'example@test.com',
    ],
    'slack' => [
        'https://hooks.slack.com/services/xxx/xxx/xxx',
    ],
],
```
> **Please note**: this README shows routes in the code for convenience, however it is recommended to set routes in environment variables that can then be read from configuration files.

Different routes might need to be notified depending on what instance of notifiable exception is thrown. Ad hoc channels and routes can be defined in notifiable exceptions themselves by overriding the method `getCustomRoutes()`:
``` php
class UrgentException extends NotifiableException
{
    protected function getCustomRoutes(): array
    {
        return [
            'nexmo' => [
                '15556666666',
            ],
        ];
    }
}
```
In the example above, the phone number `+1 555-666-6666` will receive an SMS whenever `UrgentException` is thrown.

Messages to send can be customized per channel by overriding the method `getMessages()`:
``` php
public function getMessages(): array
{
    return [
        'mail' => (new MailMessage)
            ->error()
            ->subject('An error occurred')
            ->line($this->getMessage()),
        'slack' => (new SlackMessage)
            ->error()
            ->content($content)
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title($this->getMessage())
                    ->fields([
                        'File' => $this->getFile(),
                        'Line' => $this->getLine(),
                        'Code' => $this->getCode(),
                        'Previous exception' => $this->getPrevious() ? get_class($this->getPrevious()) : 'none',
                    ]);
            }),
        'nexmo' => (new NexmoMessage)->content($this->getMessage()),
    ];
}
```

By default Laravel supports some notification channels (e.g. `mail`, `slack`), however custom channel classes need to be specified when using [third-party solutions](http://laravel-notification-channels.com). We can define them by overriding the method `getCustomChannels()`:
``` php
use NotificationChannels\Telegram\TelegramChannel;

...

public function getCustomChannels(): array
{
    return [
        'telegram' => TelegramChannel::class,
    ];
}
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email andrea.marco.sartori@gmail.com instead of using the issue tracker.

## Credits

- [Andrea Marco Sartori][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/cerbero/notifiable-exception.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/cerbero/notifiable-exception/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/cerbero/notifiable-exception.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/cerbero/notifiable-exception.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/cerbero/notifiable-exception.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/cerbero/notifiable-exception
[link-travis]: https://travis-ci.org/cerbero/notifiable-exception
[link-scrutinizer]: https://scrutinizer-ci.com/g/cerbero/notifiable-exception/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/cerbero/notifiable-exception
[link-downloads]: https://packagist.org/packages/cerbero/notifiable-exception
[link-author]: https://github.com/cerbero90
[link-contributors]: ../../contributors
