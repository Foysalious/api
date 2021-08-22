<?php namespace Sheba\Algolia\Provider;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Sheba\Dal\PartnerPosService\Events\PartnerPosServiceCreated;
use Sheba\Dal\PartnerPosService\Events\PartnerPosServiceSaved as PartnerPosServiceSaved;
use Sheba\Algolia\Listeners\PartnerPosServiceSaved as PartnerPosServiceSavedListener;
use Sheba\Dal\PartnerPosService\Events\PartnerPosServiceUpdated;

class EventsListenerProvider extends EventServiceProvider
{
    public function boot()
    {
        Event::listen(PartnerPosServiceSaved::class, PartnerPosServiceSavedListener::class);
    }
}