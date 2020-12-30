<?php namespace App\Transformers\Business;

use App\Models\Business;
use App\Models\Profile;
use App\Sheba\Business\BusinessBasicInformation;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\ApprovalFlow\Type;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\Dal\ApprovalRequest\Status;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\ApprovalRequest\ApprovalRequestPresenter as ApprovalRequestPresenter;
use Sheba\Dal\Leave\LeaveStatusPresenter as LeaveStatusPresenter;
use Sheba\Dal\LeaveLog\Contract as LeaveLogRepo;
use Sheba\Dal\LeaveStatusChangeLog\Contract as LeaveStatusChangeLogRepo;

class ApprovalRequestTransformer extends TransformerAbstract
{
    use BusinessBasicInformation;
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
     * ApprovalRequestTransformer constructor.
     * @param Profile $profile
     * @param Business $business
     * @param LeaveLogRepo $leave_log_repo
     * @param LeaveStatusChangeLogRepo $leave_status_change_log_repo
     */
    public function __construct(Profile $profile, Business $business, LeaveLogRepo $leave_log_repo, LeaveStatusChangeLogRepo $leave_status_change_log_repo)
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
        /** @var Leave $requestable */
        $requestable = $approval_request->requestable;
        $leave_type = $requestable->leaveType()->withTrashed()->first();
        $approvers = $this->getApprover($requestable);
        $business_member = $requestable->businessMember;

        return [
            'id' => $approval_request->id,
            'type' => Type::LEAVE,
            'status' => ApprovalRequestPresenter::statuses()[$approval_request->status],
            'created_at' => $approval_request->created_at->format('M d, Y'),
            'leave' => [
                'id' => $requestable->id,
                'employee_id' => $business_member->employee_id,
                'department' => $business_member->role->businessDepartment->name,
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

                'is_leave_days_exceeded' => $requestable->isLeaveDaysExceeded(),
                'period' => $requestable->start_date->format('M d') . ' - ' . $requestable->end_date->format('M d'),
                'leave_date' => ($requestable->start_date->format('M d, Y') == $requestable->end_date->format('M d, Y')) ? $requestable->start_date->format('M d, Y') : $requestable->start_date->format('M d, Y') . ' - ' . $requestable->end_date->format('M d, Y') ,
                'status' => LeaveStatusPresenter::statuses()[$requestable->status],
                'note' => $requestable->note,
            ],
            'leave_log_details' => $this->getLeaveLog($requestable),
            'approvers' => $approvers,
        ];
    }

    private function getApprover($requestable)
    {
        $approvers = [];
        $requestable->requests->each(function ($approval_request) use (&$approvers) {
            $business_member = $this->getBusinessMemberById($approval_request->approver_id);
            $member = $business_member->member;
            $profile = $member->profile;
            $approvers[] = $this->approvarWithStatus($approval_request, $profile);

        });

        return $approvers;
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
