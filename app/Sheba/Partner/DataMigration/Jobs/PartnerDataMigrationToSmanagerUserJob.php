<?php namespace Sheba\Partner\DataMigration\Jobs;


use App\Jobs\Job;
use App\Models\PartnerPosCustomer;
use App\Sheba\PosCustomerService\SmanagerUserServerClient;
use App\Sheba\UserMigration\Modules;
use App\Sheba\UserMigration\UserMigrationRepository;
use App\Sheba\UserMigration\UserMigrationService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Sheba\Dal\PartnerDataMigration\PartnerDataMigration;
use Sheba\Dal\PartnerDataMigration\Statuses;
use Sheba\Dal\UserMigration\UserStatus;

class PartnerDataMigrationToSmanagerUserJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $data;
    private $partner;
    private $queueNo;
    private $attempts = 0;

    public function __construct($partner, $data, $queueNo)
    {
        $this->connection = 'pos_rebuild_data_migration';
        $this->queue = 'pos_rebuild_data_migration';
        $this->partner = $partner;
        $this->data = $data;
        $this->queueNo = $queueNo;
    }

    public function handle()
    {
        try {
            $this->attempts < 2 ? $this->migrate() : $this->storeLogs(0);;
        } catch (\Exception $e) {
            $this->storeLogs(0);
            app('sentry')->captureException($e);
        }
    }

    private function migrate()
    {
        /** @var $client SmanagerUserServerClient */
        $client = app(SmanagerUserServerClient::class);
        $redis_smanager_user_namespace = 'DataMigration::Partner::'.$this->partner->id.'::SmanagerUser::Queue::';
        $previous_key = $redis_smanager_user_namespace . ($this->queueNo - 1);
        if ($this->isInventoryAndPosOrderQueuesProcessed() && !$this->isRedisKeyExists($previous_key)) {
            $this->increaseAttempts();
            $client->post('api/v1/partners/'.$this->partner->id.'/migrate', $this->data);
            $current_key = $redis_smanager_user_namespace . $this->queueNo;
            $this->deleteRedisKey($current_key);
            $this->storeLogs(1);
        } else {
            $this->release(10);
        }
    }

    private function isInventoryAndPosOrderQueuesProcessed(): bool
    {
        return empty(Redis::keys('DataMigration::Partner::' . $this->partner->id . '::Inventory::Queue::*')) &&
            empty(Redis::keys('DataMigration::Partner::' . $this->partner->id . '::PosOrder::Queue::*'));
    }

    private function isRedisKeyExists($key): bool
    {
        $key = Redis::get($key);
        return (bool)$key;
    }

    private function deleteRedisKey($key)
    {
        Redis::del($key);
    }

    private function increaseAttempts()
    {
        $this->attempts += 1;
    }

    private function storeLogs($isMigrated = 1)
    {
        if ($isMigrated == 0) {
            /** @var UserMigrationService $userMigrationSvc */
            $userMigrationSvc = app(UserMigrationService::class);
            /** @var UserMigrationRepository $class */
            $class = $userMigrationSvc->resolveClass(Modules::POS);
            $class->setUserId($this->partner->id)->setModuleName(Modules::POS)->updateStatus(UserStatus::FAILED);
        }
        $data_type = isset($this->data['partner_info']) ? 'partner_info' : key($this->data);
        $ids = array_column($this->data[key($this->data)], 'id');
        switch ($data_type) {
            case 'pos_customers':
                PartnerPosCustomer::whereIn('id', $ids)->update(['is_migrated' => $isMigrated]);
                break;
        }
    }
}