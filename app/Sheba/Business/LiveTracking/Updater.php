<?php namespace Sheba\Business\LiveTracking;

use App\Models\Business;
use Carbon\Carbon;
use Sheba\Dal\LiveTrackingIntervalLog\LiveTrackingIntervalLogRepository;
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
    /*** @var LiveTrackingIntervalLogRepository*/
    private $liveTrackIntervalLogRepo;

    public function __construct()
    {
        $this->liveTrackingSettingRepo = app(LiveTrackingSettingRepository::class);
        $this->liveTrackIntervalLogRepo = app(LiveTrackingIntervalLogRepository::class);
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
            $this->updateTrackIntervalTimeLog();
            $this->createTrackIntervalTimeLog();
            return $this->liveTrackingSetting;
        }
        $tracking_settings = $this->liveTrackingSettingRepo->create($this->withCreateModificationField([
            'business_id' => $this->business->id,
            'is_enable' => $this->isEnable,
            'location_fetch_interval_in_minutes' => $this->intervalTime
        ]));
        $this->createTrackIntervalTimeLog();
        return $tracking_settings;
    }

    private function updateTrackIntervalTimeLog()
    {
        $tracking_interval_log = $this->business->currentIntervalSetting();
        $tracking_interval_log->update($this->withUpdateModificationField([
            'business_id' => $this->business->id,
            'end_time' => Carbon::now()->toDateString(),
        ]));
    }

    private function createTrackIntervalTimeLog()
    {
        $this->liveTrackIntervalLogRepo->create($this->withCreateModificationField([
            'business_id' => $this->business->id,
            'start_time' => Carbon::now()->toDateString(),
        ]));
    }
}
