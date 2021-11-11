<?php

namespace App\Sheba\Business\Prorate;

use Carbon\Carbon;
use Sheba\Business\Prorate\Creator as ProrateCreator;
use Sheba\Business\Prorate\Requester as ProrateRequester;

class CalculateProrate
{
    private $businessMember;
    private $business;
    private $fiscalYear;
    private $leaveType;
    private $prorateRequester;
    private $prorateCreator;

    public function __construct()
    {
        $this->prorateRequester = app(ProrateRequester::class);
        $this->prorateCreator = app(ProrateCreator::class);
    }

    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }

    public function setBusinessFiscalYear($business_fiscal_year)
    {
        $this->fiscalYear = $business_fiscal_year;
        return $this;
    }

    public function setLeaveType($leave_type)
    {
        $this->leaveType = $leave_type;
        return $this;
    }

    public function setBusinessMember($business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function calculate()
    {
        $joining_date = Carbon::parse($this->businessMember->join_date);
        if ($joining_date->month <= $this->fiscalYear->start->month) return false;
        $remaining_month = Carbon::now()->diffInMonths($this->fiscalYear->end);
        $total_leave_days = $this->leaveType->total_days;
        $prorated_days = intval(($remaining_month / 12) * $total_leave_days);
        $this->createBusinessMemberLeaveProrate($prorated_days);
    }

    private function createBusinessMemberLeaveProrate($prorated_days)
    {

    }


}