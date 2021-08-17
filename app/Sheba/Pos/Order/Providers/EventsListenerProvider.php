<?php namespace App\Sheba\Pos\Order\Providers;

use App\Sheba\Pos\Order\Listeners\PosOrderSaved as PosOrderSavedListener;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Sheba\Dal\POSOrder\Events\PosOrderSaved as PosOrderSavedEvent;

class EventsListenerProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // TODO: Implement register() method.DalEventsListenerProvider
    }

    /**
     * @param Dispatcher $events
     */
    public function boot(Dispatcher $events)
    {
        parent::boot($events);
        $events->listen(PosOrderSavedEvent::class, PosOrderSavedListener::class);

    }
}