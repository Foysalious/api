<?php

namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Sheba\TopUp\Events\TopUpRequestOfBlockedNumber as TopUpRequestOfBlockedNumberEvent;
use Sheba\TopUp\Listeners\TopUpRequestOfBlockedNumber;
use Sheba\Dal\Profile\Events\ProfilePasswordUpdated;
use Sheba\Profile\Listeners\ProfilePasswordUpdatedListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        TopUpRequestOfBlockedNumberEvent::class => [
            TopUpRequestOfBlockedNumber::class
        ],
        ProfilePasswordUpdated::class => [
            ProfilePasswordUpdatedListener::class
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @param DispatcherContract $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        //
    }
}
