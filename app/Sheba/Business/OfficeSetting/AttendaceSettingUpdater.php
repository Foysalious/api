<?php namespace Sheba\Business\OfficeSetting;

use App\Models\Business;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;
use Sheba\Dal\BusinessOfficeHours\Contract as BusinessOfficeHoursRepoInterface;
use Sheba\ModificationFields;
use Sheba\Dal\LeaveType\Model as LeaveType;

class AttendaceSettingUpdater
{
    use ModificationFields;

    private $business;
    private $member;
    private $office_hour_type;
    private $start_time;
    private $end_time;
    private $weekends = [];
    private $weekend_repo;
    private $office_hour_repo;
    private $halfDay;
    private $halfDayConfiguration;
    private $isStartGracePeriodAllowed;
    private $isEndGracePeriodAllowed;
    private $startingGracePeriodTime;
    private $endingGracePeriodTime;

    /**
     * Updater constructor.
     * @param BusinessWeekendRepoInterface $weekend_repo
     * @param BusinessOfficeHoursRepoInterface $office_hour_repo
     */
    public function __construct(BusinessWeekendRepoInterface $weekend_repo,
                                BusinessOfficeHoursRepoInterface $office_hour_repo)
    {
        $this->weekend_repo = $weekend_repo;
        $this->office_hour_repo = $office_hour_repo;
    }

    public function setBusiness(Business $business)
    {
       $this->business = $business;
       return $this;
    }

    public function setMember(Member $member)
    {
        $this->member = $member;
        return $this;
    }

    public function setOfficeHourType($office_hour_type)
    {
        $this->office_hour_type = $office_hour_type;
        return $this;
    }

    public function setStartTime($start_time)
    {
       $this->start_time = $start_time;
       return $this;
    }

    public function setStartGracePeriod($is_start_grace_period_allowed)
    {
        $this->isStartGracePeriodAllowed = $is_start_grace_period_allowed;
        return $this;
    }

    public function setStartGracePeriodTime($starting_grace_period_time)
    {
        $this->startingGracePeriodTime = $starting_grace_period_time;
        return $this;
    }

    public function setEndGracePeriodTime($ending_grace_period_time)
    {
        $this->endingGracePeriodTime = $ending_grace_period_time;
        return $this;
    }

    public function setEndGracePeriod($is_end_grace_period_allowed)
    {
        $this->isEndGracePeriodAllowed = $is_end_grace_period_allowed;
        return $this;
    }

    public function setEndTime($end_time)
    {
       $this->end_time = $end_time;
       return $this;
    }

    public function setHalfDayTimings($request)
    {
        $this->halfDay = (int) $request->half_day;
        $request_config = json_decode($request->half_day_config, true);

        $request_config = [
            'first_half' => [
                'start_time' => Carbon::parse($request_config['first_half']['start_time'])->format('H:i').':59',
                'end_time' => Carbon::parse($request_config['first_half']['end_time'])->format('H:i').':59',
            ],
            'second_half' => [
                'start_time' => Carbon::parse($request_config['second_half']['start_time'])->format('H:i').':59',
                'end_time' => Carbon::parse($request_config['second_half']['end_time'])->format('H:i').':59',
            ]
        ];

        $request_config = json_encode($request_config);
        if ($this->halfDay) {
            $this->halfDayConfiguration = $request_config;
        }

        return $this;
    }

    public function update()
    {
        $this->setModifier($this->member);

        $update_office_hours = $this->updateOfficeHours();
        if ($this->halfDay) {
            $this->updateHalfDaySettingsForActivated();
        } else {
            $this->updateHalfDaySettingsForDeactivated();
        }
        return true;
    }

    private function updateOfficeHours()
    {
        $office_time = $this->office_hour_repo->getOfficeTime($this->business);
        $data = [
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'is_start_grace_time_enable' => $this->isStartGracePeriodAllowed,
            'is_end_grace_time_enable' => $this->isEndGracePeriodAllowed,
        ];
        
        if ($this->isStartGracePeriodAllowed == 1) $data['start_grace_time'] = $this->startingGracePeriodTime;
        if ($this->isEndGracePeriodAllowed == 1) $data['end_grace_time'] = $this->endingGracePeriodTime;

        DB::transaction(function () use ($office_time, $data){
            $this->office_hour_repo->update($office_time, $this->withUpdateModificationField($data));
        });

        return true;
    }

    private function updateHalfDaySettingsForActivated()
    {
        $data = [
            'is_half_day_enable' => 1,
            'half_day_configuration' => $this->halfDayConfiguration
        ];

        return $this->business->update($this->withUpdateModificationField($data));
    }

    private function updateHalfDaySettingsForDeactivated()
    {
        $data = ['is_half_day_enable' => 0];
        $this->business->update($this->withUpdateModificationField($data));
        LeaveType::withTrashed()->where('business_id', $this->business->id)->where('is_half_day_enable', 1)->update(['is_half_day_enable' => 0]);

        return true;
    }
}
