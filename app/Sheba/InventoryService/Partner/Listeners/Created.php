<?php namespace App\Sheba\InventoryService\Partner\Listeners;

use App\Jobs\Partner\SyncPartnersSetting;
use App\Sheba\InventoryService\Partner\Events\Created as EventsCreated;
use App\Sheba\InventoryService\Services\SyncService\PartnerInventorySetting;
use App\Sheba\PosOrderService\Services\SyncService\PartnerPosSetting;
use Illuminate\Foundation\Bus\DispatchesJobs;

class Created
{
    use DispatchesJobs;

    public function __construct()
    {
    }

    public function handle(EventsCreated $event)
    {
        $this->dispatch((new SyncPartnersSetting($event->getModel()))->setSyncService(PartnerInventorySetting::class));
        $this->dispatch((new SyncPartnersSetting($event->getModel()))->setSyncService(PartnerPosSetting::class));
    }
}