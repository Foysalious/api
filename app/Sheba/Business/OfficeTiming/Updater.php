<?php namespace Sheba\Business\OfficeTiming;

use App\Models\Business;
use App\Models\Member;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;
use Sheba\Dal\BusinessOfficeHours\Contract as BusinessOfficeHoursRepoInterface;
use Sheba\ModificationFields;

class Updater
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

    public function __construct(BusinessWeekendRepoInterface $weekend_repo,BusinessOfficeHoursRepoInterface  $office_hour_repo)
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

    public function setEndTime($end_time)
    {
       $this->end_time = $end_time;
       return $this;
    }

    public function setWeekends($weekends)
    {
       $this->weekends = $weekends;
       return $this;
    }

    public function update()
    {
        $this->setModifier($this->member);

        $update_weekends = $this->updateWeekends();
        $update_office_hours = $this->updateOfficeHours();

        return true;
    }

    private function updateWeekends()
    {
        $weekends = $this->weekend_repo->getAllByBusiness($this->business);
        if(is_null($weekends)) return "No Weekends";
        $weekends->each(function ($weekend){
            $weekend->delete();
        });
        foreach ( $this->weekends as $weekend )
        {
            $this->createWeekend($this->business->id, $weekend);
        }
        return true;
    }

    private function createWeekend($business_id , $weekend)
    {
        $data = [ 'business_id' => $business_id, 'weekday_name' => $weekend ];
        $this->weekend_repo->create($this->withCreateModificationField($data));
    }

    private function updateOfficeHours()
    {
        $office_time = $this->office_hour_repo->getOfficeTime($this->business);
        $data = [ 'start_time' => $this->start_time , 'end_time' => $this->end_time ];
        $this->office_hour_repo->update($office_time, $this->withUpdateModificationField($data));
        return true;
    }
}