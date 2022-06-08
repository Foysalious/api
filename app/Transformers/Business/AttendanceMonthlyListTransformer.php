<?php namespace App\Transformers\Business;

use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\Profile;
use Illuminate\Http\Request;
use League\Fractal\TransformerAbstract;
use Sheba\Helpers\TimeFrame;

class AttendanceMonthlyListTransformer extends TransformerAbstract
{

    /**  @var TimeFrame $timeFrame */
    private $timeFrame;

    public function setDateRange($start_date, $end_date)
    {
        $this->timeFrame = (new TimeFrame())->forDateRange($start_date, $end_date);

        return $this;
    }
    public function transform(BusinessMember $business_member)
    {
        $department = $business_member->department();
        /** @var Member $member */
        $member = $business_member->member;
        /** @var Profile $profile */
        $profile = $member->profile;

        $business_member_leave = $business_member->leaves()->accepted()->startDateBetween($this->timeFrame)->endDateBetween($this->timeFrame)->get();
        $attendances = $business_member->attendances()->whereBetween('date', $this->timeFrame->getArray())->get();

        return [
            'business_member_id' => $business_member->id,
            'employee_id' => $business_member->employee_id ?: 'N/A',
            #'email' => $profile->email,
            #'status' => $business_member->status,
            'member' => [
                'name' => $profile->name,
            ],
            'department' => [
                'id' => $department->id,
                'name' => $department->name,
            ],
            #'attendance' => $employee_attendance['statistics'],
            #'joining_prorated' => $joining_prorated ? 'Yes' : 'No'
        ];

    }
}