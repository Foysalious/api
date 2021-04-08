<?php namespace Sheba\Partner\DataMigration;

use App\Exceptions\Pos\DataAlreadyMigratedException;
use App\Models\Partner;
use Sheba\Partner\DataMigration\Jobs\PartnerMigrationCompleteJob;

class DataMigration
{
    /** @var Partner */
    private $partner;
    /** @var InventoryDataMigration */
    private $inventoryDataMigration;

    public function __construct(InventoryDataMigration $inventoryDataMigration)
    {
        $this->inventoryDataMigration = $inventoryDataMigration;
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
     * @throws DataAlreadyMigratedException
     */
    public function migrate()
    {
        if ($this->partner->isMigrationCompleted()) throw new DataAlreadyMigratedException();
        $this->inventoryDataMigration->setPartner($this->partner)->migrate();
        dispatch(new PartnerMigrationCompleteJob($this->partner));

    }
}