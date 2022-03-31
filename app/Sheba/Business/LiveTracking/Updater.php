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
    /*** @var LiveTrackingSettingRepository  */
    private $liveTrackingSettingRepo;
    private $intervalTime;
    private $liveTrackingSetting;

    public function __construct()
    {
        $this->liveTrackingSettingRepo = app(LiveTrackingSettingRepository::class);
    }

    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }

    public function setLiveTrackingSetting($live_tracking_setting)
    {
        $this->liveTrackingSetting = $live_tracking_setting;
        return $this;
    }

    public function setIsEnable($is_enable)
    {
        $this->isEnable = $is_enable;
        return $this;
    }

    public function setIntervalTime($interval_time)
    {
        $this->intervalTime = $interval_time;
        return $this;
    }

    public function update()
    {

        if ($this->liveTrackingSetting) {
            $this->liveTrackingSetting->update($this->withUpdateModificationField([
                'is_enable' => $this->isEnable,
                'location_fetch_interval_in_minutes' => $this->intervalTime
            ]));
            return $this->liveTrackingSetting;
        }
        return $this->liveTrackingSettingRepo->create($this->withUpdateModificationField([
            'business_id' => $this->business->id,
            'is_enable' => $this->isEnable,
            'location_fetch_interval_in_minutes' => $this->intervalTime
        ]));
    }
}
