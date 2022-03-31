<?php namespace Sheba\Business\LiveTracking\ChangeLogs;

use Sheba\Dal\LiveTrackingChangeLogs\Contract as LiveTrackingSettingLogsRepo;
use Sheba\ModificationFields;

class Creator
{
    use ModificationFields;
    const DEFAULT_INTERVAL_TIME = 15;
    const STATUS = [0 => 'Disabled', 1 => 'Enabled'];

    private $liveTrackingSetting;
    /*** @var LiveTrackingSettingLogsRepo */
    private $liveTrackingSettingLogsRepo;
    private $isEnable;
    private $intervalTime;
    private $previousIsEnable;
    private $previousIntervalTime;
    private $businessMember;

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

    public function setBusinessMember($business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function setIntervalTime($interval_time)
    {
        $this->intervalTime = $interval_time;
        return $this;
    }

    public function setPreviousIsEnable($previous_is_enable)
    {
        $this->previousIsEnable = $previous_is_enable;
        return $this;
    }

    public function setPreviousIntervalTime($previous_interval_time)
    {
        $this->previousIntervalTime = $previous_interval_time;
        return $this;
    }

    public function createBusinessSettingsLogs()
    {
        $data = [];
        if ($this->previousIsEnable != $this->isEnable){
            $data[] = [
                'live_track_settings_id' => $this->liveTrackingSetting->id,
                'from' => self::STATUS[!$this->isEnable],
                'to' => self::STATUS[$this->isEnable],
                'log' => 'Employee Live Visit Tracking is '.self::STATUS[$this->isEnable]
            ];
        }
        if ($this->previousIntervalTime != $this->intervalTime){
            $data[] = [
                'live_track_settings_id' => $this->liveTrackingSetting->id,
                'from' => $this->previousIntervalTime?:self::DEFAULT_INTERVAL_TIME,
                'to' => $this->intervalTime,
                'log' => 'Employee Live Visit Tracking Location Fetch Interval Time set to '.$this->intervalTime.' minutes'
            ];
        }
        $this->liveTrackingSettingLogsRepo->insert($data);
    }

    public function createEmployeeTrackingChangeLogs()
    {
        $this->liveTrackingSettingLogsRepo->create($this->withCreateModificationField([
            'live_track_settings_id' => $this->liveTrackingSetting->id,
            'from' => self::STATUS[!$this->isEnable],
            'to' => self::STATUS[$this->isEnable],
            'log' => 'Live Visit Tracking has been '.self::STATUS[$this->isEnable].' for '.$this->businessMember->profile()->name
        ]));
    }

}
