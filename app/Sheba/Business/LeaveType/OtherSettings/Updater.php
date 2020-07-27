<?php namespace Sheba\Business\LeaveType\OtherSettings;

use App\Models\Business;
use Sheba\ModificationFields;

class Updater
{
    use ModificationFields;

    private $business;
    private $member;
    private $sandwichLeave;
    private $fiscalYear;

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function setMember($member)
    {
        $this->member = $member;
        return $this;
    }

    public function setSandwichLeave($sandwich_leave)
    {
        $this->sandwichLeave = (int)$sandwich_leave;
        return $this;
    }

    public function setFiscalYear($fiscal_year)
    {
        $this->fiscalYear = (int)$fiscal_year;
        return $this;
    }

    public function update()
    {
        $data = [
            'is_sandwich_leave_enable' => $this->sandwichLeave,
            'fiscal_year' => $this->fiscalYear
        ];

       return $this->business->update($this->withUpdateModificationField($data));
    }
}