<?php namespace App\Transformers\Business;

use App\Models\Business;
use App\Models\BusinessMember;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;
use Sheba\Dal\Leave\Status;
use Sheba\Helpers\TimeFrame;

class LeaveBalanceTransformer extends TransformerAbstract
{
    private $leave_types;
    /** @var TimeFrame $timeFrame */
    private $timeFrame;
    /** @var BusinessMember $businessMember */
    private $businessMember;
    private $businessHoliday;
    private $businessWeekend;
    /** Business $business */
    private $business;
    private $business_member_leave_prorate = [];

    /**
     * LeaveBalanceTransformer constructor.
     * @param $leave_types
     * @param Business $business
     */
    public function __construct($leave_types, Business $business, $time_frame)
    {
        $this->leave_types = $leave_types;
        $this->businessHoliday = app(BusinessHolidayRepoInterface::class)->getAllDateArrayByBusiness($business);
        $this->businessWeekend = app(BusinessWeekendRepoInterface::class)->getAllByBusiness($business)->pluck('weekday_name')->toArray();
        $this->business = $business;
        $this->business_member_leave_prorate = $this->business->getBusinessMemberProrate();
        $this->timeFrame = $time_frame;
    }

    /**
     * @param Collection $business_members
     * @return array
     */
    public function transform(Collection $business_members)
    {
        $employee_wise_leave_balance = [];
        $business_members->each(function ($business_member) use (&$employee_wise_leave_balance) {
            /** @var BusinessMember $business_member */
            $this->businessMember = $business_member;

            array_push($employee_wise_leave_balance, [
                'id' => $this->businessMember->id,
                'employee_id' => $this->businessMember->employee_id ?: null,
                'department' => $this->businessMember->role ? $this->businessMember->role->businessDepartment->name : null,
                'employee_name' => $business_member->member->profile->name,
                'leave_balance' => $this->calculate()
            ]);
        });

        return $employee_wise_leave_balance;
    }

    private function calculate()
    {
        $single_employee_leave_balance = [];

        foreach ($this->leave_types as $leave_type) {
            $leaves_filtered_by_type = $this->businessMember->leaves->where('leave_type_id', $leave_type['id'])->where('status', Status::ACCEPTED);
            $used_leave_days = $this->businessMember->getCountOfUsedLeaveDaysByDateRange($leaves_filtered_by_type, $this->timeFrame, $this->businessHoliday, $this->businessWeekend);
            $total_days = $this->getTotalDays($leave_type);
            array_push($single_employee_leave_balance, [
                'id' => $leave_type['id'],
                'title' => $leave_type['title'],
                'allowed_leaves' => (int)$total_days,
                'used_leaves' => $used_leave_days,
                'is_leave_days_exceeded' => ($used_leave_days > (int)$total_days)
            ]);
        }

        return $single_employee_leave_balance;
    }

    private function getTotalDays($leave_type)
    {
        if (array_key_exists($this->businessMember->id, $this->business_member_leave_prorate)) {
            if (array_key_exists($leave_type['id'], $this->business_member_leave_prorate[$this->businessMember->id]['leave_types'])) {
                return $this->business_member_leave_prorate[$this->businessMember->id]['leave_types'][$leave_type['id']]['total_days'];
            }
        }
        return $leave_type['total_days'];
    }
}
