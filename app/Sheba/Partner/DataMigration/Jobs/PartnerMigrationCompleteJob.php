<?php namespace Sheba\Partner\DataMigration\Jobs;

use App\Jobs\Job;
use App\Sheba\UserMigration\Modules;
use App\Sheba\UserMigration\UserMigrationRepository;
use App\Sheba\UserMigration\UserMigrationService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Sheba\Dal\UserMigration\UserStatus;

class PartnerMigrationCompleteJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $partner;

    public function __construct($partner)
    {
        $this->connection = 'pos_rebuild_data_migration';
        $this->queue = 'pos_rebuild_data_migration';
        $this->partner = $partner;
    }

    public function handle()
    {
        $this->isQueuesProcessed() ? $this->storeSuccessLog() : $this->release(10);
    }

    private function isQueuesProcessed(): bool
    {
        return empty(Redis::keys('DataMigration::Partner::' . $this->partner->id . '::Inventory::Queue::*')) &&
            empty(Redis::keys('DataMigration::Partner::' . $this->partner->id . '::PosOrder::Queue::*')) &&
            empty(Redis::keys('DataMigration::Partner::' . $this->partner->id . '::SmanagerUser::Queue::*'));
    }

    /**
     * @throws Exception
     */
    private function storeSuccessLog()
    {
//        $partnerDataMigration = PartnerDataMigration::where('partner_id', $this->partner->id)->first();
//        $partnerDataMigration->update(['status' => Statuses::SUCCESSFUL]);
        /** @var UserMigrationService $userMigrationSvc */
        $userMigrationSvc = app(UserMigrationService::class);
        /** @var UserMigrationRepository $class */
        $class = $userMigrationSvc->resolveClass(Modules::POS);
        $class->setUserId($this->partner->id)->setModuleName(Modules::POS)->updateStatus(UserStatus::UPGRADED);
    }
}