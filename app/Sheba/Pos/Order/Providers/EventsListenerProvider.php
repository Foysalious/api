<?php namespace App\Sheba\Pos\Order\Providers;

use App\Sheba\Pos\Order\Listeners\PosOrderSaved as PosOrderSavedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Support\Facades\Event;
use Sheba\Algolia\Listeners\PartnerSaved;
use Sheba\Dal\POSOrder\Events\PosOrderSaved as PosOrderSavedEvent;

class EventsListenerProvider extends EventServiceProvider
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

    public function boot()
    {
        parent::boot();
        Event::listen(PosOrderSavedEvent::class, PartnerSaved::class);
    }
}
