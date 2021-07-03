<?php namespace Sheba\Partner\DataMigration;

use App\Exceptions\Pos\DataMigrationException;
use App\Models\Partner;
use App\Sheba\Partner\DataMigration\PosOrderDataMigration;
use Illuminate\Support\Facades\Redis;
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

    public function __construct(InventoryDataMigration $inventoryDataMigration, PosOrderDataMigration $posOrderDataMigration)
    {
        $this->inventoryDataMigration = $inventoryDataMigration;
        $this->posOrderDataMigration = $posOrderDataMigration;
    }

    /**
     * @param mixed $partner
     * @return DataMigration
     */
    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @throws DataMigrationException
     */
    public function migrate()
    {
        if ($this->partner->isMigrationRunningOrCompleted()) throw new DataMigrationException();
        dispatch(new PartnerMigrationStartJob($this->partner));
        $this->inventoryDataMigration->setPartner($this->partner)->migrate();
        $this->posOrderDataMigration->setPartner($this->partner)->migrate();
        dispatch(new PartnerMigrationCompleteJob($this->partner));
    }
}