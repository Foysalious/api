<?php namespace App\Console\Commands;

use App\Models\Business;
use App\Sheba\Business\LeaveProrateLogs\Creator as LeaveProrateLogCreator;
use Carbon\Carbon;
use Sheba\Dal\BusinessMemberLeaveType\Contract as BusinessMemberLeaveTypeInterface;
use Sheba\Helpers\TimeFrame;

class LeaveAdjustmentOnEndOfFiscalYear extends Command
{
    const LEAVE_PRORATE_TYPE = 'fiscal_year';
    /** @var string The name and signature of the console command. */
    protected $signature = 'sheba:leave-adjustment';

    /** @var string The console command description. */
    protected $description = 'Leave Adjustment on end of Fiscal Year';
    /*** @var BusinessMemberLeaveTypeInterface $businessMemberLeaveTypeRepo*/
    private $businessMemberLeaveTypeRepo;
    private $leaveProrateLogCreator;

    public function __construct()
    {
        $this->businessMemberLeaveTypeRepo = app(BusinessMemberLeaveTypeInterface::class);
        $this->leaveProrateLogCreator = app(LeaveProrateLogCreator::class);
        /*$knownDate = Carbon::create(2023, 01, 01, 00, 05);
        Carbon::setTestNow($knownDate);*/
        parent::__construct();
    }

    public function handle()
    {
        $businesses = Business::all();
        foreach ($businesses as $business) {
            if ($business->id != 142) continue;
            $time_frame = $this->getBusinessFiscalPeriod($business);
            $fy_start_date = $time_frame->start;
            if ($fy_start_date->toDateString() != Carbon::now()->toDateString()) continue;
            $business_member_ids = $business->getActiveBusinessMember()->pluck('id')->toArray();
            $business_members_leave_types = $this->businessMemberLeaveTypeRepo->whereIn('business_member_id', $business_member_ids)->get();
            $leave_types = $business->leaveTypes()->withTrashed()->pluck('total_days', 'id')->toArray();
            foreach ($business_members_leave_types as $business_members_leave_type)
            {
                if ($business_members_leave_type->businessMember->id != 590) continue;
                $total_days = $leave_types[$business_members_leave_type->leave_type_id];
                $previous_leave_type_total_days = $business_members_leave_type->total_days;
                $business_members_leave_type->update(['total_days' => $total_days]);
                $this->leaveProrateLogCreator->setBusinessMember($business_members_leave_type->businessMember)
                    ->setProratedType(self::LEAVE_PRORATE_TYPE)
                    ->setProratedLeaveDays($total_days)
                    ->setPreviousLeaveTypeTotalDays($previous_leave_type_total_days)
                    ->setLeaveType($business_members_leave_type)
                    ->setLeaveTypeTarget("Sheba\\Dal\\BusinessMemberLeaveType\\Model")
                    ->create();
            }

        }
    }

    public function getBusinessFiscalPeriod($business)
    {
        $business_fiscal_start_month = $business->fiscal_year ?: Business::BUSINESS_FISCAL_START_MONTH;
        $time_frame = new TimeFrame();
        return $time_frame->forAFiscalYear(Carbon::now(), $business_fiscal_start_month);
    }



}