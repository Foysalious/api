<?php namespace Sheba\Business\LiveTracking;

use App\Models\Business;
use Sheba\Dal\LiveTrackingSettings\Contract as LiveTrackingSettingRepository;
use Sheba\ModificationFields;

class Updater
{
    use ModificationFields;
    private $isEnable;
    /*** @var Business */
    private $business;
    /*** @var LiveTrackingSetting  */
    private $liveTrackingSettingRepo;

    public function __construct()
    {
        $this->liveTrackingSettingRepo = app(LiveTrackingSettingRepository::class);
    }

    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }

    public function setIsEnable($is_enable)
    {
        $this->isEnable = $is_enable;
        return $this;
    }

    public function update()
    {
        $live_tracking_setting = $this->business->liveTrackingSettings;
        if ($live_tracking_setting) {
            $live_tracking_setting->update($this->withUpdateModificationField(['is_enable' => $this->isEnable]));
            return $live_tracking_setting;
        }
        return $this->liveTrackingSettingRepo->create($this->withUpdateModificationField([
            'business_id' => $this->business->id,
            'is_enable' => $this->isEnable
        ]));
    }
}
