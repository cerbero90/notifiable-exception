<?php

namespace Cerbero\LaravelNotifiableException;

use Cerbero\LaravelNotifiableException\Notifications\ErrorOccurred;
use Cerbero\LaravelNotifiableException\Providers\LaravelNotifiableExceptionServiceProvider;
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
        return [LaravelNotifiableExceptionServiceProvider::class];
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

        $this->assertExceptionNotification(new DummyNotifiableException, function (
            ErrorOccurred $notification,
            AnonymousNotifiable $notified
        ) use ($expectedRoutes) {
            foreach ($notified->routes as $channel => $route) {
                $this->assertContains($channel, array_keys($expectedRoutes));
                $this->assertContains($route, (array) $expectedRoutes[$channel]);
            }
        });
    }

    /**
     * Specify the routes where the given exception is expected to be notified.
     *
     * @param \Cerbero\LaravelNotifiableException\Notifiable $exception
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

        $exception->notify();
    }

    /**
     * @test
     */
    public function sendsDifferentMessagesDependingOnChannel()
    {
        $this->assertExceptionNotification(new DummyNotifiableException, function (
            ErrorOccurred $notification,
            AnonymousNotifiable $notified
        ) {
            $this->assertSame('foo', $notification->toMail());
            $this->assertSame('bar', $notification->toSlack());
        });
    }

    /**
     * @test
     */
    public function deliversToCustomChannels()
    {
        $this->assertExceptionNotification(new DummyNotifiableException, function (
            ErrorOccurred $notification,
            AnonymousNotifiable $notified
        ) {
            $deliveryChannels = $notification->via($notified);

            $this->assertCount(2, $deliveryChannels);
            $this->assertContains('mail', $deliveryChannels);
            $this->assertContains('baz', $deliveryChannels);
        });
    }

    /**
     * @test
     */
    public function failsIfNoMessageCanBeSentForChannel()
    {
        $this->assertExceptionNotification(new DummyNotifiableException, function (
            ErrorOccurred $notification,
            AnonymousNotifiable $notified
        ) {
            try {
                $notification->toUnknown();
                $this->fail('An exception was expected as there are no messages to notify for the channel [unknown]');
            } catch (Exception $e) {
                $this->assertInstanceOf('RuntimeException', $e);
                $this->assertSame('The channel [unknown] does not have any message to notify.', $e->getMessage());
            }
        });
    }
}

class DummyNotifiableException extends NotifiableException
{
    protected function getAdditionalRoutes()
    {
        return [
            'mail' => 'custom1',
            'slack' => 'custom2',
        ];
    }

    public function getMessagesByChannel()
    {
        return [
            'mail' => 'foo',
            'slack' => 'bar',
        ];
    }

    public function getCustomChannels()
    {
        return [
            'slack' => 'baz',
        ];
    }
}
