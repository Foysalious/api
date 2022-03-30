<?php namespace Sheba\Business\LiveTracking\ChangeLogs;

use Sheba\Dal\LiveTrackingChangeLogs\Contract as LiveTrackingSettingLogsRepo;
use Sheba\ModificationFields;

class Creator
{
    use ModificationFields;

    const STATUS = [0 => 'Disabled', 1 => 'Enabled'];

    private $liveTrackingSetting;
    /*** @var LiveTrackingSettingLogsRepo */
    private $liveTrackingSettingLogsRepo;
    private $isEnable;

    public function __construct()
    {
        $this->liveTrackingSettingLogsRepo = app(LiveTrackingSettingLogsRepo::class);
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

    public function create()
    {
        $this->liveTrackingSettingLogsRepo->create($this->withCreateModificationField([
            'live_track_settings_id' => $this->liveTrackingSetting->id,
            'from' => self::STATUS[!$this->isEnable],
            'to' => self::STATUS[$this->isEnable],
            'log' => 'Employee Live Visit Tracking is '.self::STATUS[$this->isEnable]
        ]));
    }

}
