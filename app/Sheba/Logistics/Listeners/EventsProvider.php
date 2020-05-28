<?php namespace Sheba\Logistics\Listeners;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Sheba\Dal\Job\Events\JobSaved;
use Sheba\Dal\JobMaterial\Events\JobMaterialSaved;
use Sheba\Dal\JobService\Events\JobServiceSaved;

class EventsProvider extends EventServiceProvider
{
    /**
     * Register any other events for your application.
     *
     * @param Dispatcher $events
     * @return void
     */
    public function boot(Dispatcher $events)
    {
        parent::boot($events);

        $events->listen(JobSaved::class, JobListener::class);
        $events->listen(JobServiceSaved::class, JobServiceListener::class);
        $events->listen(JobMaterialSaved::class, JobMaterialListener::class);
    }
}
