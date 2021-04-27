<?php namespace App\Transformers\Business;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Member;
use App\Models\Profile;
use App\Sheba\Business\Leave\ApproverWithReason;
use App\Transformers\AttachmentTransformer;
use League\Fractal\TransformerAbstract;
use Sheba\Business\Leave\RejectReason\Reason;
use Sheba\Dal\ApprovalFlow\Type;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\ApprovalRequest\ApprovalRequestPresenter as ApprovalRequestPresenter;
use Sheba\Dal\Leave\LeaveStatusPresenter as LeaveStatusPresenter;
use Sheba\Dal\Leave\Status;
use Sheba\Dal\LeaveLog\Contract as LeaveLogRepo;
use Sheba\Dal\LeaveStatusChangeLog\Contract as LeaveStatusChangeLogRepo;
use Sheba\Dal\ApprovalRequest\Contract as ApprovalRequestRepository;

class LeaveRequestDetailsTransformer extends TransformerAbstract
{
    const SUPER_ADMIN = 1;
    const APPROVER = 0;

    private $business;
    /** @var Profile Profile */
    private $profile;
    private $role;
    private $leaveLogRepo;
    protected $defaultIncludes = ['attachments'];
    /**
     * @var LeaveStatusChangeLogRepo
     */
    private $leaveStatusChangeLogRepo;
    /*** @var BusinessMember */
    private $businessMember;
    /*** @var ApprovalRequestRepository */
    private $approvalRequestRepo;

    /**
     * LeaveRequestDetailsTransformer constructor.
     * @param Business $business
     * @param BusinessMember $business_member
     * @param Profile $profile
     * @param BusinessRole $role
     * @param LeaveLogRepo $leave_log_repo
     * @param LeaveStatusChangeLogRepo $leave_status_change_log_repo
     */
    public function __construct(Business $business, BusinessMember $business_member, Profile $profile, BusinessRole $role, LeaveLogRepo $leave_log_repo, LeaveStatusChangeLogRepo $leave_status_change_log_repo)
    {
        $this->business = $business;
        $this->businessMember = $business_member;
        $this->profile = $profile;
        $this->role = $role;
        $this->leaveLogRepo = $leave_log_repo;
        $this->leaveStatusChangeLogRepo = $leave_status_change_log_repo;
        $this->approvalRequestRepo = app(ApprovalRequestRepository::class);
    }

    /**
     * @param ApprovalRequest $approval_request
     * @return array
     */
    public function transform(ApprovalRequest $approval_request)
    {
        /** @var Leave $requestable */
        $requestable = $approval_request->requestable;
        $leave_type = $requestable->leaveType()->withTrashed()->first();
        $business_member = $requestable->businessMember;
        /** @var BusinessMember $substitute_business_member */
        $substitute_business_member = $requestable->substitute;
        /** @var Member $member */
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
            'super_admin_section_show' => $this->isLeaveCancelled($requestable),
            'show_approve_reject_buttons' => $this->isLeaveApprovedOrRejected($requestable),
            'super_admin_action_reason' => (new ApproverWithReason())->getRejectReason($approval_request, self::SUPER_ADMIN, null),
            'show_normal_approver_approve_reject_buttons' => $requestable->status == Status::PENDING ? $this->isApproverButtonShow($requestable) : 0,
            'leave' => [
                'id' => $requestable->id,
                'business_member_id' => $business_member->id,
                'employee_id' => $business_member->employee_id,
                'name' => $this->profile->name,
                'pro_pic' => $this->profile->pro_pic,
                'email' => $this->profile->email ?: null,
                'mobile' => $this->profile->mobile ?: null,
                'title' => $requestable->title,
                'requested_on' => $requestable->created_at->format('M d') . ' at ' . $requestable->created_at->format('h:i A'),
                'type' => [
                    'id' => $leave_type->id,
                    'title' => $leave_type->title,
                    'total_leave_days' => $leave_type->total_days,
                    ],
                    'total_days' => (int)$requestable->total_days,
                    'left' => $requestable->left_days < 0 ? abs($requestable->left_days) : $requestable->left_days,
                    'is_leave_days_exceeded' => $requestable->isLeaveDaysExceeded(),
                    'period' => $requestable->start_date->format('M d, Y') == $requestable->end_date->format('M d, Y') ? $requestable->start_date->format('M d, Y') : $requestable->start_date->format('M d, Y') . ' - ' . $requestable->end_date->format('M d, Y'),
                    'start_date' => $requestable->start_date->format('Y-m-d'),
                    'end_date' => $requestable->end_date->format('Y-m-d'),
                    'note' => $requestable->note,
                    'status' => LeaveStatusPresenter::statuses()[$requestable->status], 'is_half_day' => $requestable->is_half_day,
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
            ],
            'department' => [
                'department_id' => $this->role ? $this->role->businessDepartment->id : null,
                'department' => $this->role ? $this->role->businessDepartment->name : null,
                'designation' => $this->role ? $this->role->name : null
            ],
            'leave_log_details' => $this->getLeaveLog($requestable),
        ];
    }

    public function includeAttachments($approval_request)
    {
        $collection = $this->collection($approval_request->requestable->attachments, new AttachmentTransformer());
        return $collection->getData() ? $collection : $this->item(null, function () {
            return [];
        });
    }

    private function isLeaveCancelled($requestable)
    {
        /** @var Leave $requestable */
        if ($requestable->status === Status::CANCELED) return 0;
        if (($this->leaveLogRepo->statusUpdatedBySuperAdmin($requestable->id))) return 0;
        return 1;
    }

    private function isLeaveApprovedOrRejected($requestable)
    {
        /** @var Leave $requestable */
        $result = $requestable->where('id', $requestable->id)->whereIn('status', [Status::ACCEPTED, Status::REJECTED])->first();
        return $result ? 0 : 1;
    }

    private function isApproverButtonShow($requestable)
    {
        $result = $this->approvalRequestRepo
            ->where('requestable_id', $requestable->id)
            ->where('approver_id',$this->businessMember->id)
            ->where('status', Status::PENDING)
            ->first();
        return $result ? 1: 0;
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
