<?php namespace App\Sheba\InventoryService\Listeners;

use App\Jobs\Partner\SyncPartnersSetting;
use App\Sheba\InventoryService\Events\PartnerModelUpdated;
use App\Sheba\InventoryService\Services\SyncService\PartnerInventorySetting;
use App\Sheba\PosOrderService\Services\SyncService\PartnerPosSetting;
use Illuminate\Foundation\Bus\DispatchesJobs;

class PartnerModelUpdatedListener
{
    protected $partnerInventorySync;
    protected $partnerPosSync;
    use DispatchesJobs;

    public function __construct(PartnerInventorySetting $inventorySettingSync, PartnerPosSetting $partnerPosSync)
    {
        $this->partnerInventorySync = $inventorySettingSync;
        $this->partnerPosSync = $partnerPosSync;

    }

    public function handle(PartnerModelUpdated $event)
    {
        $this->partnerInventorySync->setModel($event->getModel())->syncSettings();
        $this->partnerPosSync->setModel($event->getModel())->syncSettings();

//        $job = (new SyncPartnerModel($event))->onQueue('partner_sync');
//        $this->dispatch($job);
    }
}