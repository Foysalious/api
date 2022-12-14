<?php namespace Sheba\Partner\DataMigration;

use App\Models\Partner;
use App\Models\PartnerPosCustomer;
use App\Models\PartnerPosService;
use App\Models\PosOrder;
use App\Sheba\Partner\DataMigration\PosOrderDataMigration;
use Exception;
use Illuminate\Support\Facades\Redis;
use Sheba\Dal\PartnerPosCategory\PartnerPosCategory;
use Sheba\ModificationFields;


class DataMigration
{
    const MAX_PARTNER_POS_CATEGORIES = 500;
    const MAX_PARTNER_POS_SERVICES = 500;
    const MAX_POS_ORDERS = 500;
    const MAX_PARTNER_POS_CUSTOMERS = 500;
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
    /**
     * @var PartnerDataMigration
     */
    private $partnerDataMigration;

    public function __construct(
        InventoryDataMigration $inventoryDataMigration,
        PosOrderDataMigration $posOrderDataMigration,
        SmanagerUserDataMigration $smanagerUserDataMigration,
        PosOrderDataMigrationChunk $posOrderDataMigrationChunk,
        PartnerDataMigration $partnerDataMigration)
    {
        $this->inventoryDataMigration = $inventoryDataMigration;
        $this->posOrderDataMigration = $posOrderDataMigration;
        $this->smanagerUserDataMigration = $smanagerUserDataMigration;
        $this->posOrderDataMigrationChunk = $posOrderDataMigrationChunk;
        $this->partnerDataMigration = $partnerDataMigration;
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

    /**
     * @throws Exception
     */
    public function migrate()
    {
        $queue_and_connection_name = $this->getMinQueue();
        $count = $this->partnerDataCount();
        $shouldQueue = $this->shouldQueue($count);
        $this->deletePreviousRedisKeys();
        $this->partnerDataMigration->setPartner($this->partner)->setIsInventoryMigrated($this->isInventoryMigrated($count))
            ->setIsPosOrderMigrated($this->isPosOrderMigrated($count))->setIsPosCustomerMigrated($this->isPosCustomerMigrated($count))->migrate();

        if (!$this->isInventoryMigrated($count)) {
            $this->inventoryDataMigration->setPartner($this->partner)
                ->setQueueAndConnectionName($queue_and_connection_name)->setShouldQueue($shouldQueue)->migrate();
        }

        if (!$this->isPosOrderMigrated($count)) {
            $this->posOrderDataMigrationChunk->setPartner($this->partner)
            ->setQueueAndConnectionName($queue_and_connection_name)->setShouldQueue($shouldQueue)->setOrderCount($count['pos_orders_count'])->generate();
        }

        if (!$this->isPosCustomerMigrated($count)) {
            $this->smanagerUserDataMigration->setPartner($this->partner)
            ->setQueueAndConnectionName($queue_and_connection_name)->setShouldQueue($shouldQueue)->migrate();
        }

        if(!$shouldQueue) {
            /** @var PartnerDataMigrationComplete $migrationComplete */
            $migrationComplete = app(PartnerDataMigrationComplete::class);
            $migrationComplete->setPartnerId($this->partner->id)->checkAndUpgrade();
        }
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

    private function isInventoryMigrated($count): bool
    {
        if ($count['partner_pos_categories_count'] == 0 && $count['partner_pos_services_count'] == 0) return true;
        return false;
    }

    private function isPosOrderMigrated($count): bool
    {
        if ($count['pos_orders_count'] == 0) return true;
        return false;
    }

    private function isPosCustomerMigrated($count): bool
    {
        if ($count['partner_pos_customers_count'] == 0) return true;
        return false;
    }

    private function partnerDataCount()
    {
        $partner_pos_categories_count = PartnerPosCategory::where('partner_id', $this->partner->id)
            ->where(function ($q) {
                $q->where('is_migrated', null)->orWhere('is_migrated', 0);
            })->withTrashed()->count();

        $partner_pos_services_count = PartnerPosService::where('partner_id', $this->partner->id)
            ->where(function ($q) {
                $q->where('is_migrated', null)->orWhere('is_migrated', 0);
            })->withTrashed()->count();

        $pos_orders_count = PosOrder::withTrashed()->where('partner_id', $this->partner->id)->where(function ($q) {
            $q->where('is_migrated', null)->orWhere('is_migrated', 0);
        })->count();

        $partner_pos_customers_count = PartnerPosCustomer::where('partner_id', $this->partner->id)->where(function ($q) {
            $q->where('is_migrated', null)->orWhere('is_migrated', 0);
        })->count();

        return [
            'partner_pos_categories_count' => $partner_pos_categories_count,
            'partner_pos_services_count' => $partner_pos_services_count,
            'pos_orders_count' => $pos_orders_count,
            'partner_pos_customers_count' => $partner_pos_customers_count
        ];
    }

    private function shouldQueue($count)
    {
        if ($count['partner_pos_categories_count'] > self::MAX_PARTNER_POS_CATEGORIES ||
            $count['partner_pos_services_count'] > self::MAX_PARTNER_POS_SERVICES ||
            $count['pos_orders_count'] > self::MAX_POS_ORDERS ||
            $count['partner_pos_customers_count'] > self::MAX_PARTNER_POS_CUSTOMERS) {
            return true;
        }
        return false;
    }

    private function deletePreviousRedisKeys()
    {
        $regex = 'DataMigration::Partner::'.$this->partner->id.'::*';
        $keys = Redis::keys($regex);
        foreach ($keys as $key) {
            Redis::del($key);
        }
    }
}