<?php namespace Sheba\Business\LeaveType\OtherSettings;

use App\Models\Business;

class BasicInfo
{
    private $business;

    public function __construct()
    {
    }

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function getInfo()
    {
        $fiscal_year_month_num = (int)$this->business->fiscal_year;
        $fiscal_year_end_month_num = $fiscal_year_month_num === 1 ? 12 : $fiscal_year_month_num - 1;
        $fiscal_year_start = $this->getMonthName($fiscal_year_month_num);
        $fiscal_year_end = $this->getMonthName($fiscal_year_end_month_num);
        return [
            'sandwich_leave' => $this->business->is_sandwich_leave_enable ? 1 : 0,
            'fiscal_year' => $fiscal_year_start . ' - ' . $fiscal_year_end
        ];
    }

    private function getMonthName($monthNum)
    {
        return date('F', mktime(0, 0, 0, $monthNum, 10));
    }

}