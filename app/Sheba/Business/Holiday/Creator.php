<?php namespace Sheba\Business\Holiday;

use App\Models\Business;
use App\Models\Member;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Sheba\ModificationFields;

class Creator
{
    use ModificationFields;

    private $business;
    private $member;
    private $holiday_repo;
    private $start_date;
    private $end_date;
    private $holiday_name;

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

    public function setHolidayRepo(BusinessHolidayRepoInterface $business_holidays_repo)
    {
        $this->holiday_repo = $business_holidays_repo;
        return $this;
    }

    public function setStartDate($start_date)
    {
        $start_date = explode('/', $start_date);
        $start_date = $start_date[2] . '-' . $start_date[1] . '-' . $start_date[0];
        $this->start_date = $start_date . ' ' . '00:00:00';
        return $this;
    }

    public function setEndDate($end_date)
    {
        $end_date = explode('/', $end_date);
        $end_date = $end_date[2] . '-' . $end_date[1] . '-' . $end_date[0];
        $this->end_date = $end_date . ' ' . '23:59:59';
        return $this;
    }

    public function setHolidayName($title)
    {
        $this->holiday_name = $title;
        return $this;
    }

    public function create()
    {
        $this->setModifier($this->member);
        $data = ["business_id" => $this->business->id, "start_date" => $this->start_date, "end_date" => $this->end_date, "title" => $this->holiday_name];
        return $this->holiday_repo->create($this->withCreateModificationField($data));
    }
}
