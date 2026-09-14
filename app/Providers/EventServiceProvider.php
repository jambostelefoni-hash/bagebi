<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        NotificationSent::class => [\App\Listeners\MarkNotificationDeliverySent::class],
        NotificationFailed::class => [\App\Listeners\MarkNotificationDeliveryFailed::class],

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Event::listen(Login::class, function (Login $event) {
            \App\Model\AuditLog::create([
                'user_id' => $event->user->id,
                'actor_name' => $event->user->name,
                'actor_email' => $event->user->email,
                'actor_role' => $event->user->role,
                'action' => 'auth.login',
                'model_type' => get_class($event->user),
                'model_id' => $event->user->id,
                'description' => 'User signed in',
                'changes' => ['role' => $event->user->role],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        Event::listen(Logout::class, function (Logout $event) {
            if (!$event->user) {
                return;
            }
            \App\Model\AuditLog::create([
                'user_id' => $event->user->id,
                'actor_name' => $event->user->name,
                'actor_email' => $event->user->email,
                'actor_role' => $event->user->role,
                'action' => 'auth.logout',
                'model_type' => get_class($event->user),
                'model_id' => $event->user->id,
                'description' => 'User signed out',
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }
}
