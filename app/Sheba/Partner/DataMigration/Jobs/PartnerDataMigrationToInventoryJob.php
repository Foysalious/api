<?php namespace Sheba\Partner\DataMigration\Jobs;


use App\Jobs\Job;
use App\Models\PartnerPosService;
use App\Models\PosCategory;
use App\Sheba\InventoryService\InventoryServerClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Sheba\Dal\PartnerDataMigration\PartnerDataMigration;
use Sheba\Dal\PartnerDataMigration\Statuses;
use Sheba\Dal\PartnerPosCategory\PartnerPosCategory;

class PartnerDataMigrationToInventoryJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $data;
    private $partner;
    private $attempts = 0;
    private $queueNo;
    private $client;

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
            $this->migrate();
        } catch (GuzzleException $e) {
            if ($this->attempts < 2) {
                $this->migrate();
            } else {
                $data_type = isset($this->data['partner_info']) ? 'partner_info' : key($this->data);
                $ids = array_column($this->data[key($this->data)], 'id');
                $this->storeFailedLogs($data_type, $ids);
            }

        }
    }

    private function migrate()
    {
        $client = app(InventoryServerClient::class);
        $redis_inventory_namespace = 'DataMigration::Partner::'.$this->partner->id.'::Inventory::Queue::';
        $previous_key = $redis_inventory_namespace . ($this->queueNo - 1);
        if (!$this->isRedisKeyExists($previous_key)) {
            $this->increaseAttempts();
            $client->post('api/v1/partners/'.$this->partner->id.'/migrate', $this->data);
            $current_key = $redis_inventory_namespace . $this->queueNo;
            $this->deleteRedisKey($current_key);
        }
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

    private function storeFailedLogs($data_type, $ids)
    {
        $partnerDataMigration = PartnerDataMigration::where('partner_id', $this->partner->id)->first();
        $partnerDataMigration->update(['status' => Statuses::FAILED]);
        switch ($data_type) {
            case 'pos_categories':
                PosCategory::whereIn('id', $ids)->update(['is_migrated' => 0]);
                break;
            case 'partner_pos_categories':
                PartnerPosCategory::whereIn('id', $ids)->update(['is_migrated' => 0]);
                break;
            case 'products':
                PartnerPosService::whereIn('id', $ids)->update(['is_migrated' => 0]);
                break;
        }
    }
}