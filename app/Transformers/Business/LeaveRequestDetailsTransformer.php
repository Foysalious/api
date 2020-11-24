<?php namespace App\Transformers\Business;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Member;
use App\Models\Profile;
use App\Transformers\AttachmentTransformer;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\ApprovalFlow\Type;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\ApprovalRequest\ApprovalRequestPresenter as ApprovalRequestPresenter;
use Sheba\Dal\Leave\LeaveStatusPresenter as LeaveStatusPresenter;
use Sheba\Dal\LeaveLog\Contract as LeaveLogRepo;

class LeaveRequestDetailsTransformer extends TransformerAbstract
{
    private $business;
    /** @var Profile Profile */
    private $profile;
    private $role;
    private $leaveLogRepo;
    protected $defaultIncludes = ['attachments'];

    public function __construct(Business $business, Profile $profile, BusinessRole $role, LeaveLogRepo $leave_log_repo)
    {
        $this->business = $business;
        $this->profile = $profile;
        $this->role = $role;
        $this->leaveLogRepo = $leave_log_repo;
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

        /** @var BusinessMember $substitute_business_member */
        $substitute_business_member = $requestable->substitute;
        /** @var Member $member */
        $substitute_member = $substitute_business_member ? $substitute_business_member->member : null;
        /** @var Profile $profile */
        $leave_substitute = $substitute_member ? $substitute_member->profile : null;

        return [
            'id' => $approval_request->id,
            'type' => Type::LEAVE,
            'status' => ApprovalRequestPresenter::statuses()[$approval_request->status],
            'created_at' => $approval_request->created_at->format('M d, Y'),
            'super_admin_override_status' => $this->checkLeaveStatus($requestable),
            'leave' => [
                'id' => $requestable->id,
                'employee_id' => $requestable->businessMember->employee_id,
                'name' => $this->profile->name,
                'pro_pic' => $this->profile->pro_pic,
                'mobile' => $this->profile->mobile ?: null,
                'title' => $requestable->title,
                'requested_on' => $requestable->created_at->format('M d') . ' at ' . $requestable->created_at->format('h:i A'),
                'type' => ['id' => $leave_type->id, 'title' => $leave_type->title],
                'total_days' => (int)$requestable->total_days,
                'left' => $requestable->left_days < 0 ? abs($requestable->left_days) : $requestable->left_days,
                'is_leave_days_exceeded' => $requestable->isLeaveDaysExceeded(),
                'period' => $requestable->start_date->format('d/m/Y') . ' - ' . $requestable->end_date->format('d/m/Y'),
                'start_date' => $requestable->start_date->format('Y-m-d'),
                'end_date' => $requestable->end_date->format('Y-m-d'),
                'note' => $requestable->note,
                'status' => LeaveStatusPresenter::statuses()[$requestable->status],
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
                ] : null,
            ],
            'department' => [
                'department_id' => $this->role ? $this->role->businessDepartment->id : null,
                'department' => $this->role ? $this->role->businessDepartment->name : null,
                'designation' => $this->role ? $this->role->name : null
            ],
            'leave_log_details' => $this->getLeaveLogDetails($requestable),
        ];
    }

    public function includeAttachments($approval_request)
    {
        $collection = $this->collection($approval_request->requestable->attachments, new AttachmentTransformer());
        return $collection->getData() ? $collection : $this->item(null, function () {
            return [];
        });
    }

    private function checkLeaveStatus($requestable)
    {
        /** @var Leave $requestable */
        if ($requestable->isAllRequestAccepted() || $requestable->isAllRequestRejected()) return 0;
        if (($this->leaveLogRepo->statusUpdatedBySuperAdmin($requestable->id))) return 0;
        return 1;
    }

    private function getLeaveLogDetails($requestable)
    {
        $logs = $this->leaveLogRepo->where('leave_id', $requestable->id)->where('type', '<>', 'leave_adjustment')->select('log', 'created_at')->get()->map(function ($log) {
            return ['log' => $log->log, 'created_at' => $log->created_at->format('h:i A - d M, Y')];
        })->toArray();
        return $logs ? $logs : null;
    }
}
