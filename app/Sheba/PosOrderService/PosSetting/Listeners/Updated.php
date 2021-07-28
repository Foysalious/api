<?php namespace App\Sheba\PosOrderService\PosSetting\Listeners;

use App\Jobs\Partner\SyncPartnersSetting;
use App\Sheba\PosOrderService\PosSetting\Events\Updated as EventsUpdated;
use App\Sheba\InventoryService\Services\SyncService\PartnerInventorySetting;
use App\Sheba\PosOrderService\Services\SyncService\PartnerPosSetting;
use Illuminate\Foundation\Bus\DispatchesJobs;

class Updated
{
    const UPDATING_ATTR_TO_LOOK = ['sms_invoice', 'auto_printing', 'printer_name', 'printer_model', 'vat_percentage'];

    protected $partnerInventorySync;
    protected $partnerPosSync;
    use DispatchesJobs;

    public function __construct()
    {
    }

    public function handle(EventsUpdated $event)
    {
        $changed_attributes = $this->lookIfTargetAttributesUpdated($event->newModel);
        if (is_null($changed_attributes)) return;

        if($this->syncPartnerPos($changed_attributes)) {
            $this->dispatch((new SyncPartnersSetting($event->newModel))->setSyncService(PartnerPosSetting::class));
        }

        if(array_key_exists('vat_percentage', $changed_attributes)) {
            $this->dispatch((new SyncPartnersSetting($event->newModel))->setSyncService((PartnerInventorySetting::class)));
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

    /**
     * @param $changed_attributes
     * @return bool
     */
    private function syncPartnerPos($changed_attributes)
    {
        $changed_data_keys = array_keys($changed_attributes);
        $key = array_search('vat_percentage',$changed_data_keys);
        if($key !== false) {
            unset($changed_data_keys[$key]);
        }
        if(array_intersect($changed_data_keys,self::UPDATING_ATTR_TO_LOOK)) {
            return true;
        }else {
            return false;
        }
    }
}