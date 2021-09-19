<?php namespace App\Sheba\Business\Leave;

use App\Models\BusinessMember;
use Carbon\Carbon;
use Sheba\Business\Leave\Breakdown\LeaveBreakdown;
use Sheba\Dal\Leave\Contract as LeaveRepoInterface;

class MonthlyLeaveDates
{
    private $leaveRepo;
    private $leaveBreakdown;
    private $businessMember;

    /**
     * @param LeaveRepoInterface $leave_repo
     * @param LeaveBreakdown $leave_breakdown
     */
    public function __construct(LeaveRepoInterface $leave_repo, LeaveBreakdown $leave_breakdown)
    {
       $this->leaveRepo = $leave_repo;
       $this->leaveBreakdown = $leave_breakdown;
    }

    /**
     * @param BusinessMember $business_member
     * @return $this
     */
    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    /**
     * @return array
     */
    public function getLeaveDates()
    {
        $month_start = Carbon::now()->startOfMonth()->toDateString();
        $month_end = Carbon::now()->endOfMonth()->toDateString();
        $leaves = $this->leaveRepo->builder()
            ->select('id', 'title', 'business_member_id', 'leave_type_id', 'start_date', 'end_date', 'is_half_day', 'half_day_configuration', 'status')
            ->where('business_member_id', $this->businessMember->id)
            ->where(function ($query) {
                $query->where('status', 'pending')->orWhere('status', 'accepted');
            })->where('start_date', '<=', $month_end)->where('end_date', '>=', $month_start)
            ->get();

        list($leaves, $leaves_date_with_half_and_full_days) = $this->leaveBreakdown->formatLeaveAsDateArray($leaves);

        $full_day_leaves = [];
        $half_day_leaves = [];

        foreach ($leaves_date_with_half_and_full_days as $date => $leaves_date_with_half_and_full_day) {
            !$leaves_date_with_half_and_full_day['is_half_day_leave'] ? $full_day_leaves[] = $leaves_date_with_half_and_full_day['date'] :
                array_push($half_day_leaves, [
                    'date' => $leaves_date_with_half_and_full_day['date'],
                    'which_half_day' => $leaves_date_with_half_and_full_day['which_half_day'],
                ]);
        }

        return [
           'full_day_leaves' => $full_day_leaves,
           'half_day_leaves' => $half_day_leaves
        ];
    }
}