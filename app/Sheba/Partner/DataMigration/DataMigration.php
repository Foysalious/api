<?php namespace Sheba\Partner\DataMigration;

use App\Models\Partner;
use App\Models\PartnerPosCustomer;
use App\Models\PartnerPosService;
use App\Models\PosOrder;
use App\Sheba\Partner\DataMigration\PosOrderDataMigration;
use Illuminate\Support\Facades\Redis;
use Sheba\Dal\PartnerPosCategory\PartnerPosCategory;
use Sheba\Dal\PartnerPosService\PartnerPosServiceRepository;
use Sheba\ModificationFields;
use Sheba\Partner\DataMigration\Jobs\PartnerMigrationCompleteJob;
use Sheba\Partner\DataMigration\Jobs\PartnerMigrationStartJob;

class DataMigration
{
    const START = 1;
    const END = 5;
    use ModificationFields;

    /** @var Partner */
    private $partner;
    /** @var InventoryDataMigration */
    private $inventoryDataMigration;
    /**
     * @var PosOrderDataMigration
     */
    private $posOrderDataMigration;
    private $smanagerUserDataMigration;
    /**
     * @var PosOrderDataMigrationChunk
     */
    private $posOrderDataMigrationChunk;

    public function __construct(InventoryDataMigration $inventoryDataMigration, PosOrderDataMigration $posOrderDataMigration, SmanagerUserDataMigration $smanagerUserDataMigration, PosOrderDataMigrationChunk $posOrderDataMigrationChunk)
    {
        $this->inventoryDataMigration = $inventoryDataMigration;
        $this->posOrderDataMigration = $posOrderDataMigration;
        $this->smanagerUserDataMigration = $smanagerUserDataMigration;
        $this->posOrderDataMigrationChunk = $posOrderDataMigrationChunk;
    }

    /**
     * @param mixed $partner
     * @return DataMigration
     */
    public function setPartner(Partner $partner): DataMigration
    {
        $this->partner = $partner;
        return $this;
    }

    public function migrate()
    {
        $queue_and_connection_name = $this->getMinQueue();
        dispatch(new PartnerMigrationStartJob($this->partner, $queue_and_connection_name));
        if (!$this->isInventoryMigrated()) $this->inventoryDataMigration->setPartner($this->partner)->setQueueAndConnectionName($queue_and_connection_name)->migrate();
        if (!$this->isPosOrderMigrated()) $this->posOrderDataMigrationChunk->setPartner($this->partner)->setQueueAndConnectionName($queue_and_connection_name)->generate();
        if (!$this->isPosCustomerMigrated()) $this->smanagerUserDataMigration->setPartner($this->partner)->setQueueAndConnectionName($queue_and_connection_name)->migrate();
        dispatch(new PartnerMigrationCompleteJob($this->partner, $queue_and_connection_name));
    }

    public function getMinQueue()
    {
        $count = [
            'pos_rebuild_data_migration_1' => (int)Redis::get('PosOrderDataMigrationCount::pos_rebuild_data_migration_1'),
            'pos_rebuild_data_migration_2' => (int)Redis::get('PosOrderDataMigrationCount::pos_rebuild_data_migration_2'),
            'pos_rebuild_data_migration_3' => (int)Redis::get('PosOrderDataMigrationCount::pos_rebuild_data_migration_3'),
            'pos_rebuild_data_migration_4' => (int)Redis::get('PosOrderDataMigrationCount::pos_rebuild_data_migration_4'),
            'pos_rebuild_data_migration_5' => (int)Redis::get('PosOrderDataMigrationCount::pos_rebuild_data_migration_5'),
        ];
        return array_keys($count, min($count))[0];
    }

    private function isInventoryMigrated(): bool
    {
        $partner_pos_service_count = PartnerPosService::where('partner_id', $this->partner->id)
            ->where(function ($q) {
                $q->where('is_migrated', null)->orWhere('is_migrated', 0);
            })->withTrashed()->count();
        $partner_pos_category = PartnerPosCategory::where('partner_id', $this->partner->id)
            ->where(function ($q) {
                $q->where('is_migrated', null)->orWhere('is_migrated', 0);
            })->withTrashed()->count();
        if ($partner_pos_service_count == 0 && $partner_pos_category == 0) return true;
        return false;
    }

    private function isPosOrderMigrated(): bool
    {
        $pos_order_count = PosOrder::withTrashed()->where('partner_id', $this->partner->id)->where(function ($q) {
            $q->where('is_migrated', null)->orWhere('is_migrated', 0);
        })->count();
        if ($pos_order_count == 0) return true;
        return false;
    }

    private function isPosCustomerMigrated(): bool
    {
        $pos_customer_count = PartnerPosCustomer::where('partner_id', $this->partner->id)->where(function ($q) {
            $q->where('is_migrated', null)->orWhere('is_migrated', 0);
        })->count();
        if ($pos_customer_count == 0) return true;
        return false;
    }
}