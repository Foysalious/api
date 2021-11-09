<?php namespace Sheba\Partner\DataMigration;

use App\Models\Partner;
use App\Sheba\Partner\DataMigration\PosOrderDataMigration;
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
        $rand = rand(self::START, self::END);
        $queue_and_connection_name = 'pos_rebuild_data_migration_' . $rand;
        dispatch(new PartnerMigrationStartJob($this->partner, $queue_and_connection_name));
        $this->inventoryDataMigration->setPartner($this->partner)->setQueueAndConnectionName($queue_and_connection_name)->migrate();
        $this->posOrderDataMigrationChunk->setPartner($this->partner)->setQueueAndConnectionName($queue_and_connection_name)->generate();
        $this->smanagerUserDataMigration->setPartner($this->partner)->setQueueAndConnectionName($queue_and_connection_name)->migrate();
        dispatch(new PartnerMigrationCompleteJob($this->partner, $queue_and_connection_name));
    }
}