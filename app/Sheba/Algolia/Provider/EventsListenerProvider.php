<?php namespace Sheba\Algolia\Provider;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Support\Facades\Event;
use Sheba\Dal\PartnerPosService\Events\PartnerPosServiceSaved as PartnerPosServiceSaved;
use Sheba\Algolia\Listeners\PosServiceSavedListener;

class EventsListenerProvider extends EventServiceProvider
{
    public function boot()
    {
        Event::listen(PartnerPosServiceSaved::class, PosServiceSavedListener::class);
    }
}