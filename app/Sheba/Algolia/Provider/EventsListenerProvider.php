<?php namespace Sheba\Algolia\Provider;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Sheba\Dal\PartnerPosService\Events\PartnerPosServiceSaved as PartnerPosServiceSaved;
use App\Sheba\Algolia\Listeners\PartnerPosServiceSavedListener;

class EventsListenerProvider extends EventServiceProvider
{
    public function boot()
    {
        Event::listen(PartnerPosServiceSaved::class, PartnerPosServiceSavedListener::class);
    }
}