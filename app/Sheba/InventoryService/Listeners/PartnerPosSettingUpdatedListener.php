<?php namespace App\Sheba\InventoryService\Listeners;

use App\Sheba\InventoryService\Events\PartnerPosSettingUpdated;
use App\Sheba\InventoryService\Services\SyncService\PartnerInventorySetting;
use App\Sheba\PosOrderService\Services\SyncService\PartnerPosSetting;

class PartnerPosSettingUpdatedListener
{
    protected $partnerInventorySync;
    protected $partnerPosSync;

    public function __construct(PartnerInventorySetting $inventorySettingSync,PartnerPosSetting $partnerPosSync )
    {
        $this->partnerInventorySync = $inventorySettingSync;
        $this->partnerPosSync = $partnerPosSync;
    }

    public function handle(PartnerPosSettingUpdated $event)
    {
        $this->partnerInventorySync->setModel($event->getModel())->syncSettings();
        $this->partnerPosSync->setModel($event->getModel())->syncSettings();
    }
}