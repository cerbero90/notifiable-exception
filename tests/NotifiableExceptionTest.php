<?php

namespace Cerbero\NotifiableException;

use Cerbero\NotifiableException\Exceptions\NotifiableException;
use Cerbero\NotifiableException\Notifications\ErrorOccurred;
use Cerbero\NotifiableException\Providers\NotifiableExceptionServiceProvider;
use Exception;
use Illuminate\Notifications\AnonymousNotifiable;
use Orchestra\Testbench\TestCase;

/**
 * The notifiable exception test.
 *
 */
class NotifiableExceptionTest extends TestCase
{
    /**
     * Retrieve the package service providers
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [NotifiableExceptionServiceProvider::class];
    }

    /**
     * @test
     */
    public function notifiesExceptionInDefaultAndAdditionalRoutes()
    {
        $this->app->make('config')->set('notifiable_exception.default_routes', [
            'mail' => 'default1',
            'slack' => 'default2',
        ]);

        $expectedRoutes = [
            'mail' => [
                'default1',
                'custom1',
            ],
            'slack' => [
                'default2',
                'custom2',
            ],
        ];

        $exception = new DummyNotifiableException;

        $this->assertExceptionNotification($exception, function (
            ErrorOccurred $notification,
            AnonymousNotifiable $notified
        ) use ($expectedRoutes) {
            foreach ($notified->routes as $channel => $route) {
                $this->assertContains($channel, array_keys($expectedRoutes));
                $this->assertContains($route, (array) $expectedRoutes[$channel]);
            }
        });

        $exception->notify();
    }

    /**
     * Specify the routes where the given exception is expected to be notified.
     *
     * @param \Cerbero\NotifiableException\Notifiable $exception
     * @param callable $callback
     * @return void
     */
    protected function assertExceptionNotification(Notifiable $exception, callable $callback)
    {
        $this->withoutNotifications();

        $this->beforeApplicationDestroyed(function () use ($exception, $callback) {
            $this->assertNotEmpty(
                $this->dispatchedNotifications,
                'No notification has been sent for exception ' . get_class($exception)
            );

            foreach ($this->dispatchedNotifications as $dispatched) {
                $notification = $dispatched['instance'];
                $notified = $dispatched['notifiable'];

                $this->assertInstanceOf(ErrorOccurred::class, $notification);
                $this->assertInstanceOf(AnonymousNotifiable::class, $notified);

                $callback($notification, $notified);
            }
        });
    }

    /**
     * @test
     */
    public function notifiesExceptionOnlyInCustomRoutesWhenOverridingRoutes()
    {
        $this->app->make('config')->set('notifiable_exception.default_routes', [
            'mail' => 'default1',
            'slack' => 'default2',
        ]);

        $expectedRoutes = [
            'mail' => [
                'custom1',
            ],
            'slack' => [
                'custom2',
            ],
        ];

        $exception = new OverridingDummyNotifiableException;

        $this->assertExceptionNotification($exception, function (
            ErrorOccurred $notification,
            AnonymousNotifiable $notified
        ) use ($expectedRoutes) {
            foreach ($notified->routes as $channel => $route) {
                $this->assertContains($channel, array_keys($expectedRoutes));
                $this->assertContains($route, (array) $expectedRoutes[$channel]);
            }
        });

        $exception->notify();
    }

    /**
     * @test
     */
    public function notifiesExceptionInDefaultAndAdditionalRoutesWhenReporting()
    {
        $this->app->make('config')->set('notifiable_exception.default_routes', [
            'mail' => 'default1',
            'slack' => 'default2',
        ]);

        $expectedRoutes = [
            'mail' => [
                'default1',
                'custom1',
            ],
            'slack' => [
                'default2',
                'custom2',
            ],
        ];

        $exception = new DummyNotifiableException;

        $this->assertExceptionNotification($exception, function (
            ErrorOccurred $notification,
            AnonymousNotifiable $notified
        ) use ($expectedRoutes) {
            foreach ($notified->routes as $channel => $route) {
                $this->assertContains($channel, array_keys($expectedRoutes));
                $this->assertContains($route, (array) $expectedRoutes[$channel]);
            }
        });

        $exception->report();
    }

    /**
     * @test
     */
    public function sendsDifferentMessagesDependingOnChannel()
    {
        $exception = new DummyNotifiableException;

        $this->assertExceptionNotification($exception, function (
            ErrorOccurred $notification,
            AnonymousNotifiable $notified
        ) {
            $this->assertSame('foo', $notification->toMail());
            $this->assertSame('bar', $notification->toSlack());
        });

        $exception->notify();
    }

    /**
     * @test
     */
    public function deliversToCustomChannels()
    {
        $exception = new DummyNotifiableException;

        $this->assertExceptionNotification($exception, function (
            ErrorOccurred $notification,
            AnonymousNotifiable $notified
        ) {
            $deliveryChannels = $notification->via($notified);

            $this->assertCount(2, $deliveryChannels);
            $this->assertContains('mail', $deliveryChannels);
            $this->assertContains('baz', $deliveryChannels);
        });

        $exception->notify();
    }

    /**
     * @test
     */
    public function failsInvokingNotExistingMethods()
    {
        $exception = new DummyNotifiableException;

        $this->assertExceptionNotification($exception, function (
            ErrorOccurred $notification,
            AnonymousNotifiable $notified
        ) {
            try {
                $notification->methodNotHandledDynamically();
                $this->fail('An exception was expected as a method not handled dynamically was invoked');
            } catch (Exception $e) {
                $this->assertInstanceOf('BadMethodCallException', $e);
                $this->assertSame('Call to undefined method Cerbero\NotifiableException\Notifications\ErrorOccurred::methodNotHandledDynamically()', $e->getMessage());
            }
        });

        $exception->notify();
    }

    /**
     * @test
     */
    public function failsIfNoMessageCanBeSentForChannel()
    {
        $exception = new DummyNotifiableException;

        $this->assertExceptionNotification($exception, function (
            ErrorOccurred $notification,
            AnonymousNotifiable $notified
        ) {
            try {
                $notification->toUnknown();
                $this->fail('An exception was expected as there are no messages to notify for the channel [unknown]');
            } catch (Exception $e) {
                $this->assertInstanceOf('RuntimeException', $e);
                $this->assertSame('No message to send to the channel [unknown].', $e->getMessage());
            }
        });

        $exception->notify();
    }

    /**
     * @test
     */
    public function sendsNoNotificationsByDefault()
    {
        $this->doesntExpectJobs(ErrorOccurred::class);

        $exception = new DefaultNotifiableException;
        $exception->notify();

        $this->assertEmpty($exception->getMessages());
        $this->assertEmpty($exception->getCustomChannels());
    }
}

class DefaultNotifiableException extends NotifiableException
{ }

class DummyNotifiableException extends NotifiableException
{
    protected function getCustomRoutes(): array
    {
        return [
            'mail' => 'custom1',
            'slack' => 'custom2',
        ];
    }

    public function getMessages(): array
    {
        return [
            'mail' => 'foo',
            'slack' => 'bar',
        ];
    }

    public function getCustomChannels(): array
    {
        return [
            'slack' => 'baz',
        ];
    }
}

class OverridingDummyNotifiableException extends DummyNotifiableException
{
    protected function overridesRoutes(): bool
    {
        return true;
    }
}
