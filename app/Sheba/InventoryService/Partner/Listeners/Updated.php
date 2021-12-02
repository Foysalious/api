<?php namespace App\Sheba\InventoryService\Partner\Listeners;

use App\Jobs\Partner\SyncPartnersSetting;
use App\Sheba\InventoryService\Partner\Events\Updated as EventUpdated;
use App\Sheba\InventoryService\Services\SyncService\PartnerInventorySetting;
use App\Sheba\PosOrderService\Services\SyncService\PartnerPosSetting;
use App\Sheba\UserMigration\Modules;
use Illuminate\Foundation\Bus\DispatchesJobs;

class Updated
{
    const UPDATING_ATTR_TO_LOOK = ['name', 'sub_domain'];

    protected $partnerInventorySync;
    protected $partnerPosSync;
    use DispatchesJobs;

    public function __construct()
    {
    }
    public function handle(EventUpdated $event)
    {
        $partner = $event->newModel->partner;
        if($partner && !$partner->isMigrated(Modules::POS)) {
            return;
        }
        $changed_attributes = $this->lookIfTargetAttributesUpdated($event->newModel);

        if (is_null($changed_attributes)) return;

        if(array_key_exists('name', $changed_attributes) || array_key_exists('sub_domain', $changed_attributes)) {
            $this->dispatch((new SyncPartnersSetting($event->newModel))->setSyncService(PartnerPosSetting::class));
        }

        if(array_key_exists('sub_domain', $changed_attributes)) {
            $this->dispatch((new SyncPartnersSetting($event->newModel))->setSyncService(PartnerInventorySetting::class));
        }
    }

    protected function lookIfTargetAttributesUpdated($updated_model)
    {
        $changed_attributes = $updated_model->getDirty();
        if(array_intersect(array_keys($changed_attributes),self::UPDATING_ATTR_TO_LOOK)) {
            return $changed_attributes;
        }
        return null;
    }
}