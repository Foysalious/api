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

    /**
     * LeaveBalanceTransformer constructor.
     * @param $leave_types
     * @param Business $business
     */
    public function __construct($leave_types, Business $business)
    {
        $this->leave_types = $leave_types;
        $this->businessHoliday = app(BusinessHolidayRepoInterface::class)->getAllDateArrayByBusiness($business);
        $this->businessWeekend = app(BusinessWeekendRepoInterface::class)->getAllByBusiness($business)->pluck('weekday_name')->toArray();
    }

    /**
     * @param Collection $members
     * @return array
     */
    public function transform(Collection $members)
    {
        $employee_wise_leave_balance = [];
        $members->each(function ($member) use (&$employee_wise_leave_balance) {
            /** @var BusinessMember $business_member */
            $this->businessMember = $member->businessMember;

            array_push($employee_wise_leave_balance, [
                'id' => $this->businessMember->id,
                'employee_name' => $member->profile->name,
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
            $used_leave_days = $this->businessMember->getCountOfUsedLeaveDaysByFiscalYear($leaves_filtered_by_type, $this->businessHoliday, $this->businessWeekend);

            array_push($single_employee_leave_balance, [
                'title' => $leave_type['title'],
                'allowed_leaves' => (int)$leave_type['total_days'],
                'used_leaves' => $used_leave_days,
                'is_leave_days_exceeded' => ($used_leave_days > (int)$leave_type['total_days'])
            ]);
        }

        return $single_employee_leave_balance;
    }
}
