<?php namespace Sheba\Logistics\Listeners;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Support\Facades\Event;
use Sheba\Dal\Job\Events\JobSaved;
use Sheba\Dal\JobMaterial\Events\JobMaterialSaved;
use Sheba\Dal\JobService\Events\JobServiceSaved;

class EventsProvider extends EventServiceProvider
{
    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Event::listen(JobSaved::class, JobListener::class);
        Event::listen(JobServiceSaved::class, JobServiceListener::class);
        Event::listen(JobMaterialSaved::class, JobMaterialListener::class);
    }
}
