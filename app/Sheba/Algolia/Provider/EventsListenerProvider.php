<?php namespace App\Sheba\Algolia\Provider;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Sheba\Dal\PartnerPosService\Events\PartnerPosServiceSaved as PartnerPosServiceSaved;
use App\Sheba\Algolia\Listeners\PartnerPosServiceSavedListener;

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
        $events->listen(PartnerPosServiceSaved::class, PartnerPosServiceSavedListener::class);
    }


}