<?php namespace App\Transformers\Business;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\Profile;
use App\Transformers\AttachmentTransformer;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Business\ApprovalSetting\FindApprovalSettings;
use Sheba\Business\ApprovalSetting\FindApprovers;
use Sheba\Dal\ApprovalRequest\ApprovalRequestPresenter as ApprovalRequestPresenter;
use Sheba\Dal\ApprovalRequest\Status as ApprovalRequestStatus;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\Leave\LeaveStatusPresenter as LeaveStatusPresenter;
use Sheba\Dal\Leave\Status as LeaveStatus;
use Sheba\Dal\LeaveType\Model as LeaveType;

class LeaveTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['attachments'];
    private $business;
    private $leaveLogDetails;
    private $leaveCancelLogDetails;
    private $isSubstituteRequired;

    /**
     * LeaveTransformer constructor.
     * @param Business $business
     * @param $leave_log_details
     * @param $is_substitute_required
     */
    public function __construct(Business $business, $leave_log_details, $is_substitute_required)
    {
        $this->business = $business;
        $this->leaveLogDetails = $leave_log_details;
        $this->isSubstituteRequired = $is_substitute_required;
    }

    public function transform(Leave $leave)
    {
        /** @var LeaveType $leave_type */
        $leave_type = $leave->leaveType;
        /** @var BusinessMember $substitute_business_member */
        $substitute_business_member = $leave->substitute;
        /** @var Member $member */
        $substitute_member = $substitute_business_member ? $substitute_business_member->member : null;
        /** @var Profile $profile */
        $leave_substitute = $substitute_member ? $substitute_member->profile : null;
        $approvers = $this->getApprover($leave);
        return [
            'title' => $leave->title,
            'leave_type' => $leave_type->title,
            'start_date' => $leave->start_date,
            'end_date' => $leave->end_date,
            'total_days' => $leave->total_days,
            'is_half_day' => $leave->is_half_day,
            'half_day_configuration' => $leave->is_half_day ? [
                'half_day' => $leave->half_day_configuration,
                'half_day_time' => $this->business->halfDayStartEnd($leave->half_day_configuration),
            ] : null,
            'time' => $leave->is_half_day ? $this->business->halfDayStartEndTime($leave->half_day_configuration) : $this->business->fullDayStartEndTime(),
            'status' => LeaveStatusPresenter::statuses()[$leave->status],
            'requested_on' => $leave->created_at,
            'note' => $leave->note,
            'substitute' => $substitute_business_member ? [
                'id' => $substitute_business_member->id,
                'name' => $leave_substitute->name,
                'pro_pic' => $leave_substitute->pro_pic,
                'designation' => $substitute_business_member->role ? $substitute_business_member->role->name : null
            ] : null,
            'approvers' => $approvers,
            'approver_count' => count($approvers),
            'leave_log_details' => $this->leaveLogDetails,
            'is_substitute_required' => $this->isSubstituteRequired,
            'is_cancelable_request' => $this->isCancelableRequest($leave->status, $leave->start_date)
        ];
    }

    public function isCancelableRequest($status, $start_date)
    {
        $current_time = Carbon::now()->format('Y m d H:i:s');
        $start_date = $start_date->format('Y m d H:i:s');

        if (!in_array($status, ['canceled', 'rejected']) && $current_time < $start_date && Carbon::now()->format('H:i:s') <= '23:59:59') return 1;

        return 0;
    }

    public function includeAttachments($leave)
    {
        $collection = $this->collection($leave->attachments, new AttachmentTransformer());
        return $collection->getData() ? $collection : $this->item(null, function () {
            return [];
        });
    }

    /**
     * @param Leave $leave
     * @return array
     */
    private function getApprover(Leave $leave)
    {
        $approvers = [];
        foreach ($leave->requests as $approval_request) {
            $business_member = $approval_request->approver;
            $member = $business_member->member;
            $profile = $member->profile;
            array_push($approvers, [
                'name' => $profile->name,
                'status' => $this->getApproverStatus($leave, $approval_request),
            ]);
        }

        return $approvers;
    }

    /**
     * @param $leave
     * @param $approval_request
     * @return string|null
     */
    private function getApproverStatus($leave, $approval_request)
    {
        if (ApprovalRequestPresenter::statuses()[$approval_request->status] !== ApprovalRequestStatus::PENDING)
            return ApprovalRequestPresenter::statuses()[$approval_request->status];
        if ($leave->status !== LeaveStatus::CANCELED && $approval_request->is_notified)
            return ApprovalRequestPresenter::statuses()[$approval_request->status];
        return null;
    }
}
