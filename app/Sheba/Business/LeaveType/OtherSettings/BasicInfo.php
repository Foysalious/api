<?php namespace Sheba\Business\LeaveType\OtherSettings;

use App\Models\Business;
use Carbon\Carbon;

class BasicInfo
{
    private $business;

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function getInfo()
    {
        $fiscal_year = $this->business->getBusinessFiscalPeriod();
        return [
            'sandwich_leave' => $this->business->is_sandwich_leave_enable ? 1 : 0,
            'fiscal_year' => $fiscal_year->start->format('F'). ' - ' .$fiscal_year->end->format('F'),
            'fiscal_year_start_month' => $fiscal_year->start->month,
            'fiscal_year_start_date' => $fiscal_year->start->format('Y-m-d'),
            'fiscal_year_end_date' => $fiscal_year->end->format('Y-m-d')
        ];
    }

}