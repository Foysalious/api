<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Sheba\WebstoreSetting\WebstoreSettingService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class WebstoreSettingsSyncJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($partner_id)
    {
        $this->partnerId = $partner_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->attempts() > 2) return;
        /** @var WebstoreSettingService $service */
        $service = app(WebstoreSettingService::class);
        $service->setPartner($this->partnerId)->sync();
    }
}
