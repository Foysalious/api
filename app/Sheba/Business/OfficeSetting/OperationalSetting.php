<?php namespace Sheba\Business\OfficeSetting;

use App\Models\Business;
use App\Models\Member;
use App\Sheba\Business\Attendance\AttendanceBasicInfo;
use Carbon\Carbon;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;
use Sheba\Dal\BusinessOfficeHours\Contract as BusinessOfficeHoursRepoInterface;
use Sheba\Dal\BusinessWeekendSettings\BusinessWeekendSettingsRepo;
use Sheba\ModificationFields;

class OperationalSetting
{
    use ModificationFields, AttendanceBasicInfo;

    private $business;
    private $member;
    private $weekends = [];
    private $weekend_repo;
    private $office_hour_repo;
    private $halfDayConfiguration;
    private $totalWorkingDaysType;
    private $numberOfDays;
    private $isWeekendIncluded;
    /*** @var BusinessWeekendSettingsRepo $businessWeekendSettingsRepo*/
    private $businessWeekendSettingsRepo;
    private $previousWeekends;

    /**
     * Updater constructor.
     * @param BusinessWeekendRepoInterface $weekend_repo
     * @param BusinessOfficeHoursRepoInterface $office_hour_repo
     */
    public function __construct(BusinessWeekendRepoInterface $weekend_repo,
                                BusinessOfficeHoursRepoInterface $office_hour_repo, BusinessWeekendSettingsRepo $business_weekend_settings_repo)
    {
        $this->weekend_repo = $weekend_repo;
        $this->office_hour_repo = $office_hour_repo;
        $this->businessWeekendSettingsRepo = $business_weekend_settings_repo;
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

    public function setPreviousWeekends($previous_weekends)
    {
        $this->previousWeekends = $previous_weekends;
        return $this;
    }

    public function setWeekends($weekends)
    {
        $this->weekends = json_decode($weekends,1);
        return $this;
    }

    private function updateWeekends()
    {
        $weekends = $this->weekend_repo->getAllByBusiness($this->business);
        if ($weekends->isEmpty() || empty($this->weekends)) return "No Weekends";
        $weekends->each(function ($weekend) {
            $weekend->delete();
        });
        foreach ($this->weekends as $weekend) {
            $this->createWeekend($this->business->id, $weekend);
        }
        $previous_weekend_string = $this->getFormattedWeekendsString($this->previousWeekends);
        $new_weekend_string = $this->getFormattedWeekendsString($this->weekends);
        if ($previous_weekend_string == $new_weekend_string) return true;
        $this->updateWeekendSettings();
        return true;
    }

    private function createWeekend($business_id , $weekend)
    {
        $data = [ 'business_id' => $business_id, 'weekday_name' => $weekend ];
        $this->weekend_repo->create($this->withCreateModificationField($data));
    }

    public function setTotalWorkingDaysType($total_working_days_type)
    {
        $this->totalWorkingDaysType = $total_working_days_type;
        return $this;
    }

    public function setNumberOfDays($number_of_days)
    {
        $this->numberOfDays = $number_of_days;
        return $this;
    }

    public function setIsWeekendIncluded($is_weekend_included)
    {
        $this->isWeekendIncluded = $is_weekend_included;
        return $this;
    }

    public function update()
    {
        $this->setModifier($this->member);
        $update_weekends = $this->updateWeekends();
        $update_office_hours = $this->updateOperationalOfficeHours();
        return true;
    }

    private function updateOperationalOfficeHours()
    {
        $office_time = $this->office_hour_repo->getOfficeTime($this->business);
        $data = ['type' => $this->totalWorkingDaysType, 'number_of_days' => $this->numberOfDays, 'is_weekend_included' => $this->isWeekendIncluded];
        $this->office_hour_repo->update($office_time, $this->withUpdateModificationField($data));

        return true;
    }

    private function updateWeekendSettings()
    {
        $business_id = $this->business->id;
        $existing_weekend_setting = $this->businessWeekendSettingsRepo->where('business_id', $business_id)->where('end_date', null)->first();
        $this->businessWeekendSettingsRepo->update($existing_weekend_setting, ['end_date' => Carbon::now()->subDay()->format('Y-m-d')]);
        $new_setting_data = [
            'business_id' => $business_id,
            'start_date' => Carbon::now()->format('Y-m-d'),
            'weekday_name' => json_encode(array_map('strtolower', $this->weekends))
        ];
        $this->businessWeekendSettingsRepo->create($new_setting_data);
    }
}
