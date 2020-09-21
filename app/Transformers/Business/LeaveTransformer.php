<?php namespace App\Transformers\Business;

use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\Profile;
use App\Transformers\AttachmentTransformer;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\ApprovalRequest\ApprovalRequestPresenter as ApprovalRequestPresenter;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\Leave\LeaveStatusPresenter as LeaveStatusPresenter;
use Sheba\Dal\LeaveType\Model as LeaveType;

class LeaveTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['attachments'];

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

        return [
            'title' => $leave->title,
            'leave_type' => $leave_type->title,
            'start_date' => $leave->start_date,
            'end_date' => $leave->end_date,
            'total_days' => $leave->total_days,
            'status' => LeaveStatusPresenter::statuses()[$leave->status],
            'requested_on' => $leave->created_at,
            'note' => $leave->note,
            'substitute' => $substitute_business_member ? [
                'name' => $leave_substitute->name
            ] : null,
            'approvers' => $this->getApprover($leave)
        ];
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
                'status' => ApprovalRequestPresenter::statuses()[$approval_request->status]
            ]);
        }

        return $approvers;
    }
}
