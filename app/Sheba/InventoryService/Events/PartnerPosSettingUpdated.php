<?php namespace App\Sheba\InventoryService\Events;

use App\Events\Event;
use App\Models\PartnerPosSetting;
use Illuminate\Queue\SerializesModels;

class PartnerPosSettingUpdated extends Event
{
     use SerializesModels;
     protected $partnerPosSetting;

     public function __construct(PartnerPosSetting $partner_pos_setting)
     {
         $this->partnerPosSetting = $partner_pos_setting;
     }

    /**
     * @return PartnerPosSetting
     */
    public function getModel()
    {
        return $this->partnerPosSetting;
    }
}