<?php namespace App\Transformers\Business;

use App\Models\Member;
use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use League\Fractal\TransformerAbstract;
use App\Models\Business;
use Sheba\Business\ApprovalSetting\FindApprovalSettings;
use Sheba\Business\ApprovalSetting\FindApprovers;
use Sheba\Dal\ApprovalFlow\Type;
use Sheba\Dal\ApprovalRequest\ApprovalRequestPresenter as ApprovalRequestPresenter;
use Sheba\Dal\ApprovalRequest\Type as ApprovalRequestType;
use Sheba\Dal\Leave\LeaveStatusPresenter as LeaveStatusPresenter;
use Sheba\Dal\Leave\Model as Leave;

class LeaveApprovalRequestListTransformer extends TransformerAbstract
{
    private $requestableType;
    private $business;

    public function __construct(Business $business)
    {
        $this->business = $business;
    }

    public function transform($approval_request)
    {
        /** @var Leave $requestable */
        $requestable = $approval_request->requestable;
        $business_member = $requestable->businessMember->load('member');
        /** @var Member $member */
        $member = $business_member->member;
        /** @var Profile $profile */
        $profile = $member->profile;
        $leave_type = $requestable->leaveType()->withTrashed()->first();
        $approvers = $this->getApprover($requestable, $business_member);

        return [
            'id' => $approval_request->id,
            'type' => Type::LEAVE,
            'status' => ApprovalRequestPresenter::statuses()[$approval_request->status],
            'created_at' => $approval_request->created_at->format('M d, Y'),
            'leave' => [
                'id' => $requestable->id,
                'business_member_id' => $business_member->id,
                'employee_id' => $business_member->employee_id,
                'department' => $business_member->role->businessDepartment->name,
                'title' => $requestable->title,
                'requested_on' => $requestable->created_at->format('M d') . ' at ' . $requestable->created_at->format('h:i a'),
                'name' => $profile->name,
                'type' => $leave_type->title,
                'total_days' => $requestable->total_days,
                'left' => $requestable->left_days < 0 ? abs($requestable->left_days) : $requestable->left_days,

                'is_half_day' => $requestable->is_half_day,
                'half_day_configuration' => $requestable->is_half_day ? [
                    'half_day' => $requestable->half_day_configuration,
                    'half_day_time' => $this->business->halfDayStartEnd($requestable->half_day_configuration),
                ] : null,
                'time' => $requestable->is_half_day ? $this->business->halfDayStartEndTime($requestable->half_day_configuration) : $this->business->fullDayStartEndTime(),

                'is_leave_days_exceeded' => $requestable->isLeaveDaysExceeded(),
                'period' => $requestable->start_date->format('M d, Y') == $requestable->end_date->format('M d, Y') ? $requestable->start_date->format('M d') : $requestable->start_date->format('M d')  . ' - ' . $requestable->end_date->format('M d'),
                'leave_date' => ($requestable->start_date->format('M d, Y') == $requestable->end_date->format('M d, Y')) ? $requestable->start_date->format('M d, Y') : $requestable->start_date->format('M d, Y') . ' - ' . $requestable->end_date->format('M d, Y'),
                'status' => LeaveStatusPresenter::statuses()[$requestable->status],
                'note' => $requestable->note
            ],
            'approvers' => $approvers
        ];
    }

    private function getApprover($requestable, $requestable_business_member)
    {
        $approvers = [];
        $all_approvers = [];
        $this->requestableType = ApprovalRequestType::getByModel($requestable);
        $approval_setting = (new FindApprovalSettings())->getApprovalSetting($requestable_business_member, $this->requestableType);
        $find_approvers = (new FindApprovers())->calculateApprovers($approval_setting, $requestable_business_member);
        $requestable_approval_request_ids = $requestable->requests()->pluck('approver_id', 'id')->toArray();
        $remainingApprovers = array_diff($find_approvers, $requestable_approval_request_ids);
        $default_approvers = (new FindApprovers())->getApproversInfo($remainingApprovers);

        foreach ($requestable->requests as $approval_request) {
            /*$business_member = $approval_request->approver;
            $member = $business_member->member;
            $profile = $member->profile;*/
            $profile = DB::table('approval_requests')
                ->join('business_member', 'business_member.id', '=', 'approval_requests.approver_id')
                ->join('members', 'members.id', '=', 'business_member.member_id')
                ->join('profiles', 'profiles.id', '=', 'members.profile_id')
                ->where('approval_requests.id', '=', $approval_request->id)
                ->first();
            array_push($approvers, [
                'name' => $profile->name ? $profile->name : 'n/s',
                'status' => ApprovalRequestPresenter::statuses()[$approval_request->status]
            ]);
        }
        $all_approvers = array_merge($approvers, $default_approvers);

        return $all_approvers;
    }
}
