<?php

namespace App\Jobs\Partner;

use App\Jobs\Job;
use App\Models\Partner;
use App\Sheba\AccountingEntry\Service\DueTrackerSmsService;
use Illuminate\Contracts\Queue\ShouldQueue;

class DueTrackerBulkSmsSend extends Job implements ShouldQueue
{
    protected $partner;
    protected $contactIds;
    protected $contactType;

    /**
     * @param Partner $partner
     * @param $contactIds
     * @param $contactType
     */
    public function __construct(Partner $partner, $contactIds, $contactType)
    {
        $this->partner = $partner;
        $this->contactIds = $contactIds;
        $this->contactType = $contactType;
    }

    public function handle()
    {
        $service = app()->make(DueTrackerSmsService::class);
        $service->setPartner($this->partner)->setContactIds($this->contactIds)
            ->setContactType($this->contactType);
    }


}