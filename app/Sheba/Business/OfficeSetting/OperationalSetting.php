<?php namespace Sheba\Business\OfficeSetting;

use App\Models\Business;
use App\Models\Member;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;
use Sheba\Dal\BusinessOfficeHours\Contract as BusinessOfficeHoursRepoInterface;
use Sheba\ModificationFields;

class OperationalSetting
{
    use ModificationFields;

    private $business;
    private $member;
    private $weekends = [];
    private $weekend_repo;
    private $office_hour_repo;
    private $halfDayConfiguration;
    private $totalWorkingDaysType;
    private $numberOfDays;
    private $isWeekendIncluded;

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
}
