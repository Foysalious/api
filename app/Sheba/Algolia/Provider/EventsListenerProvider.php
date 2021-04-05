<?php namespace Sheba\Algolia\Provider;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Support\Facades\Event;
use Sheba\Dal\PartnerPosService\Events\PartnerPosServiceSaved as PartnerPosServiceSavedEvent;
use App\Sheba\Algolia\Listeners\PartnerPosServiceSaved as PartnerPosServiceSavedListener;

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
        Event::listen(PartnerPosServiceSavedEvent::class, PartnerPosServiceSavedListener::class);
    }
}
