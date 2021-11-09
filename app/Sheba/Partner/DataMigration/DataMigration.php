<?php namespace Sheba\Partner\DataMigration;

use App\Models\Partner;
use App\Sheba\Partner\DataMigration\PosOrderDataMigration;
use Sheba\ModificationFields;
use Sheba\Partner\DataMigration\Jobs\PartnerMigrationCompleteJob;
use Sheba\Partner\DataMigration\Jobs\PartnerMigrationStartJob;

class DataMigration
{
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
        dispatch(new PartnerMigrationStartJob($this->partner));
        $this->inventoryDataMigration->setPartner($this->partner)->migrate();
        $this->posOrderDataMigrationChunk->setPartner($this->partner)->generate();
        $this->smanagerUserDataMigration->setPartner($this->partner)->migrate();
        dispatch(new PartnerMigrationCompleteJob($this->partner));
    }
}