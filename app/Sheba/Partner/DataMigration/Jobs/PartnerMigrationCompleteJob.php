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

    public function __construct($partner, $queue_and_connection_name)
    {
        $this->connection = $queue_and_connection_name;
        $this->queue = $queue_and_connection_name;
        $this->partner = $partner;
    }

    public function handle()
    {
        $this->isQueuesProcessed() ? $this->storeSuccessLog() : $this->release(10);
    }

    private function isQueuesProcessed(): bool
    {
        return empty(Redis::keys('DataMigration::Partner::' . $this->partner->id . '::Inventory::Queue::*')) &&
            empty(Redis::keys('DataMigration::Partner::' . $this->partner->id . '::PosOrderChunk::Queue::*')) &&
            empty(Redis::keys('DataMigration::Partner::' . $this->partner->id . '::PosOrder::Queue::*')) &&
            empty(Redis::keys('DataMigration::Partner::' . $this->partner->id . '::SmanagerUser::Queue::*'));
    }

    /**
     * @throws Exception
     */
    private function storeSuccessLog()
    {
        /** @var UserMigrationService $userMigrationSvc */
        $userMigrationSvc = app(UserMigrationService::class);
        /** @var UserMigrationRepository $class */
        $class = $userMigrationSvc->resolveClass(Modules::POS);
        $current_status = $class->setUserId($this->partner->id)->setModuleName(Modules::POS)->getStatus();
        if ($current_status == UserStatus::UPGRADING) $class->updateStatus(UserStatus::UPGRADED);
    }
}