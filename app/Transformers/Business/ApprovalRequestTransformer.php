<?php namespace App\Transformers\Business;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Profile;
use App\Sheba\Business\BusinessBasicInformation;
use App\Sheba\Business\Leave\ApproverWithReason;
use League\Fractal\TransformerAbstract;
use Sheba\Business\ApprovalSetting\FindApprovalSettings;
use Sheba\Business\ApprovalSetting\FindApprovers;
use Sheba\Business\Leave\RejectReason\Reason;
use Sheba\Dal\ApprovalFlow\Type;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\Dal\ApprovalRequest\Status;
use Sheba\Dal\ApprovalRequest\Type as ApprovalRequestType;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\ApprovalRequest\ApprovalRequestPresenter as ApprovalRequestPresenter;
use Sheba\Dal\Leave\LeaveStatusPresenter as LeaveStatusPresenter;
use Sheba\Dal\LeaveLog\Contract as LeaveLogRepo;
use Sheba\Dal\LeaveStatusChangeLog\Contract as LeaveStatusChangeLogRepo;

class ApprovalRequestTransformer extends TransformerAbstract
{
    use BusinessBasicInformation;

    const SUPER_ADMIN = 1;
    const APPROVER = 0;

    /** @var Profile $profile */
    private $profile;
    /** @var Business $business */
    private $business;
    /**
     * @var LeaveLogRepo
     */
    private $leaveLogRepo;
    /**
     * @var LeaveStatusChangeLogRepo
     */
    private $leaveStatusChangeLogRepo;
    /**
     * @var string
     */
    private $requestableType;
    /*** @var ApprovalRequest */
    private $approvalRequest;

    /**
     * ApprovalRequestTransformer constructor.
     * @param Profile $profile
     * @param Business $business
     * @param LeaveLogRepo $leave_log_repo
     * @param LeaveStatusChangeLogRepo $leave_status_change_log_repo
     */
    public function __construct(Profile $profile, Business $business)
    {
        $this->profile = $profile;
        $this->business = $business;
        $this->leaveLogRepo = app(LeaveLogRepo::class);
        $this->leaveStatusChangeLogRepo = app(LeaveStatusChangeLogRepo::class);
    }

    /**
     * @param ApprovalRequest $approval_request
     * @return array
     */
    public function transform(ApprovalRequest $approval_request)
    {
        $this->approvalRequest = $approval_request;
        /** @var Leave $requestable */
        $requestable = $approval_request->requestable;
        $leave_type = $requestable->leaveType()->withTrashed()->first();
        $approvers = $this->getApprover($requestable);
        $business_member = $requestable->businessMember;
        $substitute_business_member = $requestable->substitute;
        $substitute_member = $substitute_business_member ? $substitute_business_member->member : null;
        /** @var Profile $profile */
        $leave_substitute = $substitute_member ? $substitute_member->profile : null;
        $leave_substitute_role = $substitute_business_member ? $substitute_business_member->role : null;
        $leave_substitute_department = $substitute_business_member ? $substitute_business_member->department() : null;

        return [
            'id' => $approval_request->id,
            'type' => Type::LEAVE,
            'status' => ApprovalRequestPresenter::statuses()[$approval_request->status],
            'created_at' => $approval_request->created_at->format('M d, Y'),
            'leave' => [
                'id' => $requestable->id,
                'business_member_id' => $business_member->id,
                'employee_id' => $business_member->employee_id,
                'department' => $business_member->department()->name,
                'title' => $requestable->title,
                'requested_on' => $requestable->created_at->format('M d') . ' at ' . $requestable->created_at->format('h:i a'),
                'name' => $this->profile->name,
                'type' => $leave_type->title,
                'total_days' => $requestable->total_days,
                'left' => $requestable->left_days < 0 ? abs($requestable->left_days) : $requestable->left_days,
                'is_half_day' => $requestable->is_half_day,
                'half_day_configuration' => $requestable->is_half_day ? [
                    'half_day' => $requestable->half_day_configuration,
                    'half_day_time' => $this->business->halfDayStartEnd($requestable->half_day_configuration),
                ] : null,
                'time' => $requestable->is_half_day ? $this->business->halfDayStartEndTime($requestable->half_day_configuration) : $this->business->fullDayStartEndTime(),
                'substitute' => $substitute_business_member ? [
                    'id' => $substitute_business_member->id,
                    'name' => $leave_substitute->name,
                    'pro_pic' => $leave_substitute->pro_pic,
                    'mobile' => $leave_substitute->mobile ? $leave_substitute->mobile : null,
                    'email' => $leave_substitute->email,
                    'department' => $leave_substitute_department? $leave_substitute_department->name : null,
                    'designation' => $leave_substitute_role ? $leave_substitute_role->name : null,
                ] : null,
                'is_leave_days_exceeded' => $requestable->isLeaveDaysExceeded(),
                'leave_date' => ($requestable->start_date->format('M d, Y') == $requestable->end_date->format('M d, Y')) ? $requestable->start_date->format('M d, Y') : $requestable->start_date->format('M d, Y') . ' - ' . $requestable->end_date->format('M d, Y'),
                'status' => LeaveStatusPresenter::statuses()[$requestable->status],
                'note' => $requestable->note,
                'period' => $requestable->start_date->format('M d, Y') == $requestable->end_date->format('M d, Y') ? $requestable->start_date->format('M d') :$requestable->start_date->format('M d') . ' - ' . $requestable->end_date->format('M d'),
                'total_leave_days' => $leave_type->total_days,
                'super_admin_action_reason' =>(new ApproverWithReason())->getRejectReason($this->approvalRequest, self::SUPER_ADMIN, null)
            ],
            'leave_log_details' => $this->getLeaveLog($requestable),
            'approvers' => $approvers,
        ];
    }

    private function getApprover($requestable)
    {
        $approvers = [];
        $all_approvers = [];
        /** @var BusinessMember $leave_business_member */
        $this->requestableType = ApprovalRequestType::getByModel($requestable);
        $requestable_business_member = $requestable->businessMember;
        $approval_setting = (new FindApprovalSettings())->getApprovalSetting($requestable_business_member, $this->requestableType);
        $find_approvers = (new FindApprovers())->calculateApprovers($approval_setting, $requestable_business_member);
        $requestable_approval_request_ids = $requestable->requests()->pluck('approver_id', 'id')->toArray();
        $remainingApprovers = array_diff($find_approvers, $requestable_approval_request_ids);
        $default_approvers = (new FindApprovers())->getApproversInfo($remainingApprovers);
        foreach ($requestable->requests as $approval_request) {
            $business_member = $approval_request->approver;
            $member = $business_member->member;
            $profile = $member->profile;
            array_push($approvers, [
                'name' => $profile->name,
                'status' => $requestable->status !== Status::CANCELED ? ApprovalRequestPresenter::statuses()[$approval_request->status] : null,
                'reject_reason' => (new ApproverWithReason())->getRejectReason($this->approvalRequest, self::APPROVER, $business_member->id)
            ]);
        }
        $all_approvers = array_merge($approvers, $default_approvers);

        return $all_approvers;
        /*$requestable->requests->each(function ($approval_request) use (&$approvers) {
            $business_member = $this->getBusinessMemberById($approval_request->approver_id);
            $member = $business_member->member;
            $profile = $member->profile;
            $approvers[] = $this->approvarWithStatus($approval_request, $profile);

        });
        return $approvers;*/
    }

    /**
     * @param $approval_request
     * @param $profile
     * @return array
     */
    private function approvarWithStatus($approval_request, $profile)
    {
        if ($approval_request->status == Status::ACCEPTED) return ['name' => $profile->name, 'status' => ApprovalRequestPresenter::statuses()[$approval_request->status]];
        if ($approval_request->status == Status::REJECTED) return ['name' => $profile->name, 'status' => ApprovalRequestPresenter::statuses()[$approval_request->status]];
        return ['name' => $profile->name, 'status' => ApprovalRequestPresenter::statuses()[$approval_request->status]];
    }

    private function getLeaveLogDetails($requestable)
    {
        $logs = $this->leaveLogRepo->where('leave_id', $requestable->id)->where('type', '<>', 'leave_adjustment')->select('log', 'created_at')->get()->map(function ($log) {
            return ['log' => $log->log, 'created_at' => $log->created_at->format('h:i A - d M, Y')];
        })->toArray();
        return $logs ? $logs : null;
    }

    private function getLeaveCancelLogDetails($requestable)
    {
        $logs = $this->leaveStatusChangeLogRepo->where('leave_id', $requestable->id)->select('log', 'created_at')->orderBy('id', 'DESC')->get()->map(function ($log) {
            return ['log' => $log->log, 'created_at' => $log->created_at->format('h:i A - d M, Y')];
        })->toArray();
        return $logs ? $logs : null;
    }

    private function getLeaveLog($requestable)
    {
        $cancel_log = $this->getLeaveCancelLogDetails($requestable) ? $this->getLeaveCancelLogDetails($requestable) : [];
        $update_log = $this->getLeaveLogDetails($requestable) ? $this->getLeaveLogDetails($requestable) : [];

        return array_merge($cancel_log, $update_log);
    }
}
