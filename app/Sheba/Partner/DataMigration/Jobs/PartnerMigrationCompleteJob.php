<?php namespace Sheba\Partner\DataMigration\Jobs;

use App\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Sheba\Dal\PartnerDataMigration\PartnerDataMigration;
use Sheba\Dal\PartnerDataMigration\Statuses;

class PartnerMigrationCompleteJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $partner;

    public function __construct($partner)
    {
        $this->partner = $partner;
    }

    public function handle()
    {
        if ($this->isQueuesProcessed()) $this->storeSuccessLog();
    }

    private function isQueuesProcessed(): bool
    {
        return empty(Redis::keys('DataMigration::Partner::' . $this->partner->id . '::Inventory::Queue::*')) &&
            empty(Redis::keys('DataMigration::Partner::' . $this->partner->id . '::PosOrder::Queue::*'));
    }

    private function storeSuccessLog()
    {
        $partnerDataMigration = PartnerDataMigration::where('partner_id', $this->partner->id)->first();
        $partnerDataMigration->update(['status' => Statuses::SUCCESSFUL]);
    }
}